<?php
namespace App\Services\Integrations;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PayPalGateway
{
    private function configured(): bool
    {
        return (bool) config('services.paypal.client_id') && (bool) config('services.paypal.secret');
    }

    private function baseUrl(): string
    {
        return config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    private function accessToken(): string
    {
        if (!$this->configured()) {
            throw new RuntimeException('PayPal is not configured.');
        }
        $response = Http::asForm()
            ->withBasicAuth(config('services.paypal.client_id'), config('services.paypal.secret'))
            ->timeout(15)
            ->post($this->baseUrl().'/v1/oauth2/token', ['grant_type' => 'client_credentials']);
        $response->throw();
        return (string) $response->json('access_token');
    }

    public function createOrder(Order $order): array
    {
        $token = $this->accessToken();
        $requestId = 'fh-'.$order->id.'-'.Str::lower(Str::random(16));
        $response = Http::withToken($token)
            ->withHeaders(['PayPal-Request-Id' => $requestId])
            ->timeout(20)
            ->post($this->baseUrl().'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => (string) $order->order_number,
                    'amount' => [
                        'currency_code' => config('services.paypal.currency', 'PHP'),
                        'value' => number_format((float) $order->grand_total, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'return_url' => config('services.paypal.return_url'),
                    'cancel_url' => config('services.paypal.cancel_url'),
                ],
            ]);
        $response->throw();
        return $response->json();
    }

    public function capture(string $paypalOrderId): array
    {
        $response = Http::withToken($this->accessToken())
            ->timeout(20)
            ->post($this->baseUrl().'/v2/checkout/orders/'.rawurlencode($paypalOrderId).'/capture', []);
        $response->throw();
        return $response->json();
    }

    public function refund(string $captureId, ?string $amount = null): array
    {
        $body = $amount === null ? new \stdClass() : ['amount' => [
            'value' => number_format((float) $amount, 2, '.', ''),
            'currency_code' => config('services.paypal.currency', 'PHP'),
        ]];
        $response = Http::withToken($this->accessToken())
            ->timeout(20)
            ->post($this->baseUrl().'/v2/payments/captures/'.rawurlencode($captureId).'/refund', $body);
        $response->throw();
        return $response->json();
    }
}
