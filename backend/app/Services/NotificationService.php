<?php
namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Services\Integrations\FcmService;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationService
{
    public function __construct(private FcmService $fcm) {}

    public function orderStatus(Order $order, string $status): void
    {
        $order->loadMissing('customer.deviceTokens');
        if (!$order->customer) return;
        $title = 'Order '.$order->order_number;
        $message = 'Your order status is now '.str_replace('_', ' ', $status).'.';
        $order->customer->notify(new \App\Notifications\OrderStatusNotification($order, $status));
        $this->push($order->customer, $title, $message, ['order_id'=>$order->id,'status'=>$status]);
    }

    public function notifyUser(User $user, string $title, string $message, array $extra=[]): void
    {
        $user->loadMissing('deviceTokens');
        $user->notify(new \App\Notifications\GenericNotification($title, $message, $extra));
        $this->push($user, $title, $message, $extra);
    }

    private function push(User $user, string $title, string $message, array $data=[]): void
    {
        foreach ($user->deviceTokens as $device) {
            try {
                $this->fcm->send($device->token, $title, $message, $data);
                $device->forceFill(['last_seen_at'=>now()])->save();
            } catch (Throwable $e) {
                Log::warning('FoodHub FCM delivery failed', ['user_id'=>$user->id,'device_id'=>$device->device_id,'error'=>$e->getMessage()]);
            }
        }
    }
}
