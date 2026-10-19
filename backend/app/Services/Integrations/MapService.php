<?php
namespace App\Services\Integrations;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MapService
{
    public function directionsUrl(float $fromLat, float $fromLng, float $toLat, float $toLng): string
    {
        return 'https://www.google.com/maps/dir/?api=1&origin='.rawurlencode($fromLat.','.$fromLng).'&destination='.rawurlencode($toLat.','.$toLng).'&travelmode=driving';
    }

    public function geocode(string $address): array
    {
        $key = config('services.google_maps.key');
        if (!$key) throw new RuntimeException('Google Maps API key is not configured.');
        $response = Http::timeout(15)->get('https://maps.googleapis.com/maps/api/geocode/json', ['address'=>$address,'key'=>$key]);
        $response->throw();
        $json = $response->json();
        if (($json['status'] ?? '') !== 'OK' || empty($json['results'][0])) throw new RuntimeException('Address could not be geocoded.');
        $result = $json['results'][0];
        return ['formatted_address'=>$result['formatted_address'] ?? $address, 'latitude'=>$result['geometry']['location']['lat'] ?? null, 'longitude'=>$result['geometry']['location']['lng'] ?? null, 'place_id'=>$result['place_id'] ?? null];
    }
}
