<?php
namespace App\Services;
use App\Models\{Order, PaymentWebhookEvent};
use App\Services\LoyaltyWalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\Integrations\{GcashGateway, PayPalGateway};
class PaymentService {
 public function methods(): array { return [
  ['code'=>'cash','name'=>'Cash on delivery / counter','mode'=>'manual'],
  ['code'=>'gcash','name'=>'GCash','mode'=>'gateway'],
  ['code'=>'card','name'=>'Card','mode'=>'gateway'],
  ['code'=>'paypal','name'=>'PayPal','mode'=>'gateway'],
  ['code'=>'wallet','name'=>'FoodHub Wallet','mode'=>'internal'],
 ]; }
 public function initiate(Order $order,string $method): array {
  if (!in_array($method,array_column($this->methods(),'code'),true)) throw ValidationException::withMessages(['method'=>'Unsupported payment method.']);
  $payment=$order->payment;
  if (!$payment) { $payment=$order->payment()->create(['method'=>$method,'amount'=>$order->grand_total,'status'=>'pending','reference'=>'FH-PAY-'.Str::upper(Str::random(10))]); }
  else { $payment->update(['method'=>$method,'amount'=>$order->grand_total,'status'=>'pending']); }
  if ($method === 'wallet') {
   throw ValidationException::withMessages(['method'=>'Wallet payments are finalized during checkout.']);
  }
  if ($method === 'paypal') {
   $gateway = app(PayPalGateway::class);
   $paypal = $gateway->createOrder($order);
   $payment->update(['reference'=>$paypal['id'] ?? $payment->reference,'provider_payload'=>$paypal]);
   $approval = collect($paypal['links'] ?? [])->firstWhere('rel','approve');
   return ['payment'=>$payment->fresh(),'next_action'=>'redirect','provider'=>'paypal','provider_order_id'=>$paypal['id'] ?? null,'approval_url'=>$approval['href'] ?? null];
  }
  if ($method === 'gcash') {
   $gateway = app(GcashGateway::class);
   $gcash = $gateway->createCheckout($order);
   $payment->update(['reference'=>$gcash['reference'] ?? $payment->reference,'provider_payload'=>$gcash]);
   return ['payment'=>$payment->fresh(),'next_action'=>'redirect','provider'=>'gcash','checkout_url'=>$gcash['checkout_url'] ?? ($gcash['redirect_url'] ?? null)];
  }
  return ['payment'=>$payment->fresh(),'next_action'=>$method==='cash'?'cash_collection':'gateway_configuration_required'];
 }
 public function markPaid(Order $order,string $reference=null,array $payload=[]): Order {
  return DB::transaction(function() use($order,$reference,$payload){
   $payment=$order->payment()->lockForUpdate()->firstOrFail();
   $payment->update(['status'=>'paid','reference'=>$reference ?: $payment->reference,'provider_payload'=>$payload,'paid_at'=>now()]);
   $order->update(['payment_status'=>'paid']);
   return $order->fresh()->load('payment');
  });
 }
 public function refund(Order $order,array $payload=[]): Order {
  $payment=$order->payment()->firstOrFail();
  if($payment->status!=='paid') throw ValidationException::withMessages(['payment'=>'Only paid payments can be refunded.']);
  $payment->update(['status'=>'refunded','provider_payload'=>$payload]);
  $order->update(['payment_status'=>'refunded']);
  if ($payment->method === 'wallet') {
   app(LoyaltyWalletService::class)->credit($order->customer, (float) $payment->amount, 'refund', 'Wallet refund for '.$order->order_number, $order);
  }
  return $order->fresh()->load('payment');
 }
 public function markFailed(Order $order,array $payload=[]): Order {
  $order->payment()->update(['status'=>'failed','provider_payload'=>$payload]);
  $order->update(['payment_status'=>'failed']);
  return $order->fresh()->load('payment');
 }
}
