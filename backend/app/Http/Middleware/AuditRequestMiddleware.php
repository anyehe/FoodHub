<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditRequestMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $user = $request->user();

        if ($user && $user->role !== 'customer' && $request->is('api/v1/*') && $request->method() !== 'GET') {
            $payload = $request->except(['password', 'password_confirmation', 'token', 'secret', 'client_secret']);

            AuditLog::create([
                'user_id' => $user->id,
                'role' => $user->role,
                'method' => $request->method(),
                'route' => $request->path(),
                'action' => $request->route()?->getName() ?? $request->path(),
                'status_code' => $response->getStatusCode(),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'payload' => $payload,
            ]);
        }

        return $response;
    }
}
