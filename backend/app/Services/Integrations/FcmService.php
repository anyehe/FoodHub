<?php
namespace App\Services\Integrations;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class FcmService
{
    private function credentials(): array
    {
        $json = config('services.firebase.service_account_json');
        $path = config('services.firebase.credentials_path');
        if (!$json && $path && is_file($path)) $json = file_get_contents($path);
        $data = is_array($json) ? $json : json_decode((string) $json, true);
        if (!$data && config('services.firebase.client_email') && config('services.firebase.private_key')) { $data = ['client_email'=>config('services.firebase.client_email'),'private_key'=>config('services.firebase.private_key')]; }
        if (!is_array($data) || empty($data['client_email']) || empty($data['private_key'])) {
            throw new RuntimeException('Firebase service account credentials are not configured.');
        }
        return $data;
    }

    private function accessToken(): string
    {
        $credentials = $this->credentials();
        $now = time();
        $header = rtrim(strtr(base64_encode(json_encode(['alg'=>'RS256','typ'=>'JWT'])), '+/', '-_'), '=');
        $claim = rtrim(strtr(base64_encode(json_encode([
            'iss'=>$credentials['client_email'],
            'scope'=>'https://www.googleapis.com/auth/firebase.messaging',
            'aud'=>'https://oauth2.googleapis.com/token',
            'iat'=>$now,
            'exp'=>$now+3600,
        ])), '+/', '-_'), '=');
        $unsigned = $header.'.'.$claim;
        if (!openssl_sign($unsigned, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Unable to sign Firebase service account assertion.');
        }
        $jwt = $unsigned.'.'.rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        $response = Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
            'grant_type'=>'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion'=>$jwt,
        ]);
        $response->throw();
        return (string) $response->json('access_token');
    }

    public function send(string $token, string $title, string $body, array $data=[]): array
    {
        $project = config('services.firebase.project_id');
        if (!$project) throw new RuntimeException('Firebase project ID is not configured.');
        $response = Http::withToken($this->accessToken())->timeout(20)->post(
            'https://fcm.googleapis.com/v1/projects/'.$project.'/messages:send',
            ['message'=>['token'=>$token,'notification'=>['title'=>$title,'body'=>$body],'data'=>array_map('strval',$data)]]
        );
        $response->throw();
        return $response->json();
    }
}
