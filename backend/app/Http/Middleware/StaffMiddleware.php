<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $allowedRoles = [
            'admin',
            'branch_manager',
            'kitchen',
            'waiter',
        ];

        if (!in_array($user->role, $allowedRoles, true)) {
            return response()->json([
                'message' => 'Staff access required.',
                'role' => $user->role,
            ], 403);
        }

        return $next($request);
    }
}
