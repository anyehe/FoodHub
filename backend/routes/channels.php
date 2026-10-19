<?php
use Illuminate\Support\Facades\Broadcast;
Broadcast::channel('orders.{order}', function ($user, $order) {
    return $user->role === 'admin' || \App\Models\Order::whereKey($order)->where(function($q) use ($user) { $q->where('customer_id',$user->id)->orWhere('branch_id',$user->branch_id); })->exists();
});
