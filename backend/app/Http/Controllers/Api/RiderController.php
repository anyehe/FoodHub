<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{DeliveryAssignment,RiderLocationPoint};
use App\Services\OrderService;
use Illuminate\Http\Request;

class RiderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function queue(Request $request)
    {
        return DeliveryAssignment::with('order.branch', 'order.address', 'order.customer', 'order.items')
            ->where('rider_id', $request->user()->id)
            ->whereIn('status', ['offered', 'accepted', 'picked_up'])
            ->latest()
            ->get();
    }

    private function own(Request $request, int $id): DeliveryAssignment
    {
        return DeliveryAssignment::whereKey($id)
            ->where('rider_id', $request->user()->id)
            ->firstOrFail();
    }

    public function accept(Request $request, int $id)
    {
        $assignment = $this->own($request, $id);
        abort_unless($assignment->status === 'offered', 422, 'Assignment is no longer available.');

        $assignment->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return $assignment->fresh()->load('order');
    }

    public function pickup(Request $request, int $id)
    {
        $assignment = $this->own($request, $id);
        abort_unless($assignment->status === 'accepted', 422, 'Assignment must be accepted before pickup.');

        $assignment->update([
            'status' => 'picked_up',
            'picked_up_at' => now(),
        ]);

        $this->orders->transition($assignment->order, 'picked_up', $request->user(), 'Order picked up from branch');

        return $assignment->fresh()->load('order');
    }

    public function outForDelivery(Request $request, int $id)
    {
        $assignment = $this->own($request, $id);
        abort_unless($assignment->status === 'picked_up', 422, 'Order must be picked up first.');

        $this->orders->transition($assignment->order, 'out_for_delivery', $request->user(), 'Rider started delivery');

        return $assignment->fresh()->load('order');
    }

    public function location(Request $request, int $id)
    {
        $assignment = $this->own($request, $id);
        $data = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $assignment->update([
            'last_latitude' => $data['latitude'],
            'last_longitude' => $data['longitude'],
        ]);

        RiderLocationPoint::create(['delivery_assignment_id'=>$assignment->id,'rider_id'=>$request->user()->id,'latitude'=>$data['latitude'],'longitude'=>$data['longitude'],'recorded_at'=>now()]);

        return [
            'message' => 'Location updated',
            'latitude' => (float) $assignment->last_latitude,
            'longitude' => (float) $assignment->last_longitude,
        ];
    }


    public function availability(Request $request)
    {
        $data=$request->validate(['is_online'=>'required|boolean']);
        $request->user()->update(['is_online'=>$data['is_online']]);
        return ['is_online'=>(bool)$request->user()->fresh()->is_online];
    }

    public function locations(Request $request, int $id)
    {
        $assignment=$this->own($request,$id);
        return $assignment->locations()->latest('recorded_at')->limit(100)->get();
    }

    public function deliver(Request $request, int $id)
    {
        $assignment = $this->own($request, $id);
        abort_unless($assignment->status === 'picked_up' || $assignment->order->status === 'out_for_delivery', 422, 'Order is not ready to be delivered.');

        if ($assignment->order->status === 'picked_up') {
            $this->orders->transition($assignment->order, 'out_for_delivery', $request->user(), 'Delivery started at drop-off stage');
        }

        $assignment->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        $order = $this->orders->transition($assignment->order, 'delivered', $request->user(), 'Order delivered');

        if ($order->payment?->method === 'cash' && in_array($order->payment_status, ['unpaid', 'pending'], true)) {
            $order->update(['payment_status' => 'paid']);
            $order->payment?->update(['status' => 'paid', 'paid_at' => now()]);
        }

        return $assignment->fresh()->load('order.payment');
    }
}
