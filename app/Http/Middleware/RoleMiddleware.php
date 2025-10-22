<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = Session::get('user');

        if (!$user || !in_array($user['role'], $roles)) {
            return redirect()->route('login')->with('error', 'Access denied.');
        }

        return $next($request);
    }
}
