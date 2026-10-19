<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index(Request $request)
    {
        return $request->user()->orders()
            ->with(['items', 'branch', 'payment', 'assignment.rider', 'statusHistory.user'])
            ->latest()
            ->paginate(15);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'address_id' => 'nullable|exists:addresses,id',
            'fulfillment_type' => 'required|in:delivery,pickup,dine_in',
            'scheduled_at' => 'nullable|date',
            'coupon' => 'nullable|string',
            'payment_method' => 'required|in:cash,gcash,card,paypal,wallet',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:50',
            'items.*.options' => 'nullable|array',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        $order = $this->orders->create($data, $request->user()->id);
        return response()->json($order, 201);
    }

    public function show(Request $request, Order $order)
    {
        $user = $request->user();
        $canView = $order->customer_id === $user->id
            || in_array($user->role, ['admin', 'branch_manager', 'kitchen', 'waiter'], true)
            || ($user->role === 'rider' && $order->assignment?->rider_id === $user->id);

        abort_unless($canView, 403);

        return $order->load(
            'items',
            'branch',
            'address',
            'payment',
            'assignment.rider',
            'statusHistory.user'
        );
    }

    public function tracking(Request $request, Order $order)
    {
        abort_unless($order->customer_id === $request->user()->id, 403);

        $order->load([
            'branch',
            'address',
            'payment',
            'assignment.rider',
            'statusHistory.user',
            'items',
        ]);

        return [
            'order' => $order,
            'assignment' => $order->assignment,
            'rider_location' => $order->assignment ? [
                'latitude' => $order->assignment->last_latitude,
                'longitude' => $order->assignment->last_longitude,
                'updated_at' => optional($order->assignment->updated_at)->toIso8601String(),
            ] : null,
            'locations' => $order->assignment?->locations()
                ->latest('recorded_at')
                ->limit(30)
                ->get(),
        ];
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->customer_id === $request->user()->id, 403);
        abort_if(in_array($order->status,['ready','assigned','picked_up','out_for_delivery','delivered','completed'],true),422,'This order can no longer be cancelled from the customer portal.');
        abort_if($order->payment_status==='paid',422,'A paid order cannot be cancelled automatically. Please contact support for a refund.');
        return $this->orders->transition($order, 'cancelled', $request->user(), 'Cancelled by customer');
    }
}
