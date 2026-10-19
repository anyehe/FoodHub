<?php
namespace App\Notifications;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class OrderStatusNotification extends Notification {
 use Queueable;
 public function __construct(public Order $order, public string $status) {}
 public function via($notifiable): array { return ['database','mail']; }
 public function toDatabase($notifiable): array { return ['title'=>'Order '.$this->order->order_number,'message'=>'Your order status is now '.str_replace('_',' ',$this->status).'.','order_id'=>$this->order->id,'order_number'=>$this->order->order_number,'status'=>$this->status]; }
 public function toMail($notifiable): MailMessage { return (new MailMessage)->subject('FoodHub order '.$this->order->order_number)->line('Your order status is now '.str_replace('_',' ',$this->status).'.'); }
}
