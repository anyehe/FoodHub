<?php
namespace App\Services\Integrations;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GcashGateway
{
    public function createCheckout(Order $order): array
    {
        $url = config('services.gcash.checkout_url');
        $secret = config('services.gcash.secret');
        if (!$url || !$secret) throw new RuntimeException('GCash gateway is not configured.');
        $response = Http::withToken($secret)->timeout(20)->post($url, [
            'reference' => $order->order_number,
            'amount' => number_format((float) $order->grand_total, 2, '.', ''),
            'currency' => 'PHP',
            'description' => 'FoodHub '.$order->order_number,
            'return_url' => config('services.gcash.return_url'),
            'cancel_url' => config('services.gcash.cancel_url'),
        ]);
        $response->throw();
        return $response->json();
    }
}
