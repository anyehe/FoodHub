<?php
namespace App\Http\Middleware; use Closure; use Illuminate\Http\Request; class RiderMiddleware { public function handle(Request $request, Closure $next){abort_unless($request->user()?->role==='rider',403); return $next($request);}}
