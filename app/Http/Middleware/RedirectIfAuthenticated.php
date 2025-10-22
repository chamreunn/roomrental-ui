<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (Session::has('api_token') && Session::has('user')) {
            $role = Session::get('user.role');

            return match ($role) {
                'admin' => redirect()->route('dashboard.admin'),
                'manager' => redirect()->route('dashboard.manager'),
                'user' => redirect()->route('dashboard.user'),
                default => redirect()->route('home'),
            };
        }

        return $next($request);
    }
}
