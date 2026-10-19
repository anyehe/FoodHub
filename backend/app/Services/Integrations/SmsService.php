<?php
namespace App\Services\Integrations;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SmsService
{
    public function send(string $to, string $message): array
    {
        $url = config('services.sms.url');
        $token = config('services.sms.token');
        if (!$url || !$token) throw new RuntimeException('SMS provider is not configured.');
        $response = Http::withToken($token)->timeout(15)->post($url, ['to'=>$to,'message'=>$message]);
        $response->throw();
        return $response->json();
    }
}
