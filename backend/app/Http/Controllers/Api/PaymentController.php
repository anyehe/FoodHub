<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Order, PaymentWebhookEvent};
use App\Services\PaymentService;
use App\Services\Integrations\PayPalGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class PaymentController extends Controller
{
    public function methods(PaymentService $payments)
    {
        return $payments->methods();
    }

    public function initiate(Request $request, Order $order, PaymentService $payments)
    {
        abort_unless($order->customer_id === $request->user()->id, 403);
        $data = $request->validate(['method' => 'required|string']);
        return $payments->initiate($order, $data['method']);
    }


    public function paypalCapture(Request $request, Order $order, PaymentService $payments, PayPalGateway $paypal)
    {
        abort_unless($order->customer_id === $request->user()->id, 403);
        abort_unless($order->payment?->method === 'paypal', 422, 'Order is not using PayPal.');
        $paypalOrderId = (string) ($order->payment->reference ?? '');
        abort_unless($paypalOrderId !== '', 422, 'PayPal order reference is missing.');
        $result = $paypal->capture($paypalOrderId);
        if (($result['status'] ?? null) === 'COMPLETED') {
            $captureId = data_get($result, 'purchase_units.0.payments.captures.0.id');
            return $payments->markPaid($order, $captureId ?: $paypalOrderId, $result);
        }
        return ['payment'=>$order->payment->fresh(),'provider'=>$result];
    }

    public function refund(Request $request, Order $order, PaymentService $payments)
    {
        abort_unless($request->user()->role === 'admin', 403);
        $data = $request->validate(['reason' => 'nullable|string|max:500']);
        return $payments->refund($order, [
            'reason' => $data['reason'] ?? null,
            'refunded_by' => $request->user()->id,
        ]);
    }

    public function webhook(Request $request, PaymentService $payments)
    {
        $secret = (string) config('services.foodhub_webhook_secret');
        abort_unless($secret !== '', 503, 'Payment webhook secret is not configured.');

        $signature = (string) $request->header('X-FoodHub-Signature');
        $expected = hash_hmac('sha256', $request->getContent(), $secret);
        abort_unless($signature !== '' && hash_equals($expected, $signature), 401, 'Invalid webhook signature.');

        $data = $request->validate([
            'event_id' => 'required|string|max:150',
            'provider' => 'nullable|string|max:50',
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|in:paid,failed',
            'reference' => 'nullable|string|max:150',
            'amount' => 'nullable|numeric|min:0',
            'payload' => 'nullable|array',
        ]);

        try {
            return DB::transaction(function () use ($data, $payments) {
                $existing = PaymentWebhookEvent::where('event_id', $data['event_id'])->lockForUpdate()->first();
                if ($existing) {
                    return response()->json([
                        'message' => 'Duplicate webhook ignored',
                        'duplicate' => true,
                        'event_id' => $existing->event_id,
                        'order_id' => $existing->order_id,
                    ]);
                }

                $order = Order::with('payment')->lockForUpdate()->findOrFail($data['order_id']);

                if ($data['amount'] !== null && $order->payment
                    && abs((float) $data['amount'] - (float) $order->payment->amount) > 0.009) {
                    abort(422, 'Webhook amount does not match the payment amount.');
                }

                $event = PaymentWebhookEvent::create([
                    'event_id' => $data['event_id'],
                    'provider' => $data['provider'] ?? 'foodhub',
                    'order_id' => $order->id,
                    'status' => $data['status'],
                    'payload' => $data['payload'] ?? [],
                ]);

                $result = $data['status'] === 'paid'
                    ? $payments->markPaid($order, $data['reference'] ?? null, $data['payload'] ?? [])
                    : $payments->markFailed($order, $data['payload'] ?? []);

                $event->update(['processed_at' => now()]);

                return [
                    'message' => 'Payment webhook processed',
                    'duplicate' => false,
                    'event_id' => $event->event_id,
                    'order' => $result,
                ];
            });
        } catch (QueryException $e) {
            if ((string) $e->getCode() !== '23000') {
                throw $e;
            }

            $existing = PaymentWebhookEvent::where('event_id', $data['event_id'])->first();
            if ($existing) {
                return response()->json([
                    'message' => 'Duplicate webhook ignored',
                    'duplicate' => true,
                    'event_id' => $existing->event_id,
                    'order_id' => $existing->order_id,
                ]);
            }

            throw $e;
        }
    }
}
