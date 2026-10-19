<?php
namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderRealtimeUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;
    public function __construct(public Order $order) {}
    public function broadcastOn(): array { return [new PrivateChannel('orders.'.$this->order->id)]; }
    public function broadcastAs(): string { return 'order.updated'; }
    public function broadcastWith(): array { return ['order_id'=>$this->order->id,'order_number'=>$this->order->order_number,'status'=>$this->order->status,'payment_status'=>$this->order->payment_status]; }
}
