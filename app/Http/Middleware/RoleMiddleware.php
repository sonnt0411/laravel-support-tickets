<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (!$user || ($user->role !== 'admin' && !in_array($user->role, $roles))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
