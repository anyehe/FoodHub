<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{DeliveryAssignment, Order, User};
use App\Services\OrderService;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function orders(Request $request)
    {
        $query = Order::with(['items', 'customer', 'branch', 'assignment.rider', 'statusHistory.user'])->latest();

        if ($request->user()->branch_id) {
            $query->where('branch_id', $request->user()->branch_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return $query->paginate(30);
    }

    public function transition(Request $request, Order $order)
    {
        abort_unless(in_array($request->user()->role, ['admin', 'branch_manager', 'kitchen', 'waiter'], true), 403);

        abort_unless(
            !$request->user()->branch_id || $request->user()->branch_id === $order->branch_id,
            403
        );

        $data = $request->validate([
            'status' => 'required|string',
            'note' => 'nullable|string|max:500',
        ]);

        return $this->orders->transition(
            $order,
            $data['status'],
            $request->user(),
            $data['note'] ?? null
        );
    }

    public function assignRider(Request $request, Order $order)
    {
        abort_unless(
            !$request->user()->branch_id || $request->user()->branch_id === $order->branch_id,
            403
        );

        abort_unless(in_array($request->user()->role, ['admin', 'branch_manager'], true), 403, 'Only administrators and branch managers can assign riders.');

        $data = $request->validate([
            'rider_id' => 'required|exists:users,id',
        ]);

        $rider = User::whereKey($data['rider_id'])
            ->where('role', 'rider')
            ->where('is_active', true)
            ->firstOrFail();

        if ($order->status === 'ready') {
            $this->orders->transition($order, 'assigned', $request->user(), 'Rider assigned');
        } else {
            abort_unless($order->status === 'assigned', 422, 'Order must be ready before rider assignment.');
        }

        $assignment = DeliveryAssignment::updateOrCreate(
            ['order_id' => $order->id],
            ['rider_id' => $rider->id, 'status' => 'offered']
        );

        return $assignment->fresh()->load('rider', 'order');
    }

    public function dashboard(Request $request)
    {
        $query = Order::query();

        if ($request->user()->branch_id) {
            $query->where('branch_id', $request->user()->branch_id);
        }

        return [
            'today_orders' => (clone $query)->whereDate('created_at', today())->count(),
            'pending' => (clone $query)->whereIn('status', ['pending', 'confirmed', 'preparing'])->count(),
            'revenue' => (float) (clone $query)->whereDate('created_at', today())->where('payment_status', 'paid')->sum('grand_total'),
            'ready' => (clone $query)->where('status', 'ready')->count(),
            'out_for_delivery' => (clone $query)->whereIn('status', ['assigned', 'picked_up', 'out_for_delivery'])->count(),
            'recent_orders' => (clone $query)->with(['customer', 'branch', 'payment'])->latest()->limit(8)->get(),
        ];
    }
}
