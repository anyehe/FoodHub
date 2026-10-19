<?php
namespace App\Services;

class IntegrationStatusService
{
    public function status(): array
    {
        return [
            'paypal' => (bool) config('services.paypal.client_id') && (bool) config('services.paypal.secret'),
            'gcash' => (bool) config('services.gcash.checkout_url') && (bool) config('services.gcash.secret'),
            'firebase' => (bool) config('services.firebase.project_id') && ((bool) config('services.firebase.service_account_json') || (bool) config('services.firebase.credentials_path')),
            'google_maps' => (bool) config('services.google_maps.key'),
            'sms' => (bool) config('services.sms.url') && (bool) config('services.sms.token'),
            'realtime' => (bool) config('broadcasting.default'),
        ];
    }
}
