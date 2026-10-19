<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class RoleMiddleware {
    public function handle(Request $request, Closure $next, ...$roles) {
        $user=$request->user();
        abort_unless($user && in_array($user->role,$roles,true),403,'You do not have permission for this area.');
        return $next($request);
    }
}
