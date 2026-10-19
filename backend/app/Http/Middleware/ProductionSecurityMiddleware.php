<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductionSecurityMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (filter_var(env('APP_FORCE_HTTPS', false), FILTER_VALIDATE_BOOL) && !$request->isSecure()) {
            $url = 'https://'.$request->getHost().($request->getPort() && $request->getPort() !== 80 ? ':'.$request->getPort() : '').$request->getRequestUri();
            return redirect()->away($url, 301);
        }

        $response = $next($request);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(self), camera=(self), microphone=()');
        $response->headers->set('X-XSS-Protection', '0');
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
        return $response;
    }
}
