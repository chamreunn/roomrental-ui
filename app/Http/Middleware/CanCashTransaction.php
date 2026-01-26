<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CanCashTransaction
{
    public function handle($request, \Closure $next)
    {
        $role = session('user.role');
        $canCash = (int) session('user.can_cash_transaction', 0);

        if ($role === 'admin' || $canCash === 1) {
            return $next($request);
        }

        // ✅ redirect + alert message (flash)
        return redirect()
            ->route('dashboard.user') // or route('home') / route('settings.index')
            ->with('error', __('auth.no_permission_cash_transaction'));
    }
}
