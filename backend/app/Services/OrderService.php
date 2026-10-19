<?php

namespace App\Services;

use App\Models\{Address, Branch, Coupon, Order, Payment, Product, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Events\OrderRealtimeUpdated;

class OrderService
{
    public function __construct(private PricingService $pricing, private NotificationService $notifications, private LoyaltyWalletService $loyaltyWallet) {}

    public function create(array $data, int $customerId): Order
    {
        return DB::transaction(function () use ($data, $customerId) {
            $branch = Branch::where('is_active', true)->findOrFail($data['branch_id']);
            $fulfillment = $data['fulfillment_type'] ?? 'delivery';

            if ($fulfillment === 'delivery' && empty($data['address_id'])) {
                throw ValidationException::withMessages([
                    'address_id' => 'A delivery address is required.',
                ]);
            }

            if (!empty($data['address_id']) && !Address::whereKey($data['address_id'])
                ->where('user_id', $customerId)->exists()) {
                throw ValidationException::withMessages([
                    'address_id' => 'Selected address does not belong to this customer.',
                ]);
            }

            if (!empty($data['scheduled_at']) && now()->gte($data['scheduled_at'])) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'Scheduled time must be in the future.',
                ]);
            }

            $items = [];

            foreach ($data['items'] as $line) {
                $product = Product::with('options')
                    ->where('is_active', true)
                    ->findOrFail($line['product_id']);

                $pivot = $branch->products()->where('products.id', $product->id)->first();

                if (!$pivot || !$pivot->pivot->is_available) {
                    throw ValidationException::withMessages([
                        'items' => "Product {$product->name} is unavailable at this branch.",
                    ]);
                }

                $options = $line['options'] ?? [];
                $optionTotal = 0;
                $optionSnapshots = [];

                foreach ($options as $optionId) {
                    $option = $product->options->firstWhere('id', (int) $optionId);

                    if (!$option) {
                        throw ValidationException::withMessages([
                            'items' => 'Invalid product option.',
                        ]);
                    }

                    $optionTotal += (float) $option->price;
                    $optionSnapshots[] = [
                        'id' => $option->id,
                        'name' => $option->name,
                        'price' => (float) $option->price,
                    ];
                }

                $unit = (float) ($pivot->pivot->price ?? $product->base_price) + $optionTotal;

                $items[] = [
                    'product' => $product,
                    'unit_price' => $unit,
                    'quantity' => (int) $line['quantity'],
                    'options' => $optionSnapshots,
                    'notes' => $line['notes'] ?? null,
                ];
            }

            $coupon = null;

            if (!empty($data['coupon'])) {
                $coupon = Coupon::where('code', strtoupper(trim($data['coupon'])))->lockForUpdate()->first();

                if (!$coupon || !$coupon->usable()) {
                    throw ValidationException::withMessages([
                        'coupon' => 'Coupon is invalid or expired.',
                    ]);
                }
            }

            $priced = $this->pricing->calculate(
                array_map(fn ($item) => [
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                ], $items),
                $coupon,
                $fulfillment
            );

            $order = Order::create([
                'order_number' => 'FH-' . now()->format('ymdHis') . '-' . Str::upper(Str::random(4)),
                'customer_id' => $customerId,
                'branch_id' => $branch->id,
                'address_id' => $data['address_id'] ?? null,
                'fulfillment_type' => $fulfillment,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'scheduled_at' => $data['scheduled_at'] ?? null,
                ...$priced,
                'notes' => $data['notes'] ?? null,
                'placed_at' => now(),
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'options' => $item['options'],
                    'line_total' => $item['unit_price'] * $item['quantity'],
                    'notes' => $item['notes'],
                ]);
            }

            if ($coupon) {
                $coupon->increment('used_count');
            }

            $paymentMethod = $data['payment_method'] ?? 'cash';
            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $paymentMethod,
                'amount' => $order->grand_total,
                'status' => $paymentMethod === 'wallet' ? 'paid' : 'pending',
                'reference' => 'FH-PAY-' . Str::upper(Str::random(8)),
                'paid_at' => $paymentMethod === 'wallet' ? now() : null,
            ]);
            if ($paymentMethod === 'wallet') {
                $this->loyaltyWallet->debit(User::findOrFail($customerId), (float) $order->grand_total, 'Wallet payment for '.$order->order_number, $order);
                $order->update(['payment_status' => 'paid']);
            }

            $this->recordHistory($order, 'pending', $customerId, 'Order placed');
            $this->notifications->orderStatus($order, 'pending');
            OrderRealtimeUpdated::dispatch($order->fresh());

            return $order->load('items', 'payment', 'branch', 'address', 'statusHistory');
        });
    }

    public function transition(Order $order, string $status, ?User $actor = null, ?string $note = null): Order
    {
        $allowed = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['preparing', 'cancelled'],
            'preparing' => ['ready'],
            'ready' => ['assigned', 'completed'],
            'assigned' => ['picked_up', 'cancelled'],
            'picked_up' => ['out_for_delivery'],
            'out_for_delivery' => ['delivered'],
            'delivered' => ['completed'],
        ];

        if (!in_array($status, $allowed[$order->status] ?? [], true)) {
            abort(422, "Cannot move order from {$order->status} to {$status}.");
        }

        $updates = ['status' => $status];

        if ($status === 'completed' && in_array($order->payment_status, ['unpaid', 'pending'], true)) {
            if ($order->payment?->method !== 'cash') {
                throw ValidationException::withMessages([
                    'payment' => 'The order cannot be completed until the selected payment method is confirmed as paid.',
                ]);
            }
            $updates['payment_status'] = 'paid';
        }

        $order->update($updates);

        if ($status === 'completed' && $order->payment_status === 'paid' && $order->payment) {
            $order->payment->update([
                'status' => 'paid',
                'paid_at' => $order->payment->paid_at ?? now(),
            ]);
            $this->loyaltyWallet->rewardForOrder($order->fresh());
        }

        $this->recordHistory($order, $status, $actor?->id, $note);
        $this->notifications->orderStatus($order, $status);
        OrderRealtimeUpdated::dispatch($order->fresh());

        return $order->fresh()->load('items', 'payment', 'assignment.rider', 'statusHistory.user');
    }

    private function recordHistory(Order $order, string $status, ?int $userId, ?string $note = null): void
    {
        $order->statusHistory()->create([
            'status' => $status,
            'changed_by' => $userId,
            'note' => $note,
        ]);
    }
}
