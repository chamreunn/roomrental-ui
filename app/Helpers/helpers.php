<?php

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;


if (!function_exists('userRole')) {
    /**
     * Get the current logged-in user's role from session.
     *
     * @return string|null
     */
    function userRole(): ?string
    {
        $user = Session::get('user');
        return $user['role'] ?? null;
    }
}

if (!function_exists('canManageRooms')) {
    function canManageRooms(): bool
    {
        return in_array(userRole(), ['manager', 'admin']);
    }
}


if (!function_exists('apiBaseUrl')) {
    /**
     * Determine the correct API base URL depending on the environment.
     *
     * @return string
     */
    function apiBaseUrl(): string
    {
        $host = Request::getHost();

        // Local environment (e.g., localhost or 127.0.0.1)
        if (in_array($host, ['127.0.0.1', 'localhost'])) {
            return env('API_LOCAL', 'http://127.0.0.1:8000/api/');
        }

        // Production or staging
        return env('API_HOSTING', 'https://your-production-api.com/api/');
    }
}

if (!function_exists('dashboardRoute')) {
    /**
     * Get the correct dashboard route based on the user's role.
     *
     * @return string
     */
    function dashboardRoute(): string
    {
        return match (userRole()) {
            'admin' => route('dashboard.admin'),
            'manager' => route('dashboard.manager'),
            'user' => route('dashboard.user'),
            default => route('home'),
        };
    }
}

if (!function_exists('page_title')) {
    /**
     * Get the page title automatically.
     *
     * @param bool $withAppName Whether to append the app name (default: true)
     * @return string
     */
    function page_title(bool $withAppName = true): string
    {
        $appName = config('app.name');

        // Get current route name, fallback to 'home'
        $routeName = Route::currentRouteName() ?? 'home';

        // Translation key: dots -> underscores
        $titleKey = 'titles.' . str_replace('.', '_', $routeName);

        // Try translation first
        $title = __($titleKey);

        // If translation doesn't exist, fallback to human-readable
        if ($title === $titleKey) {
            $title = ucwords(str_replace('.', ' ', $routeName));
        }

        // Append app name if requested
        return $withAppName ? $title . ' | ' . $appName : $title;
    }
}

if (!function_exists('active_class')) {
    function active_class($routes, $class = 'active', $show = false)
    {
        $active = false;

        if (is_array($routes)) {
            foreach ($routes as $route) {
                if (Route::is($route)) {
                    $active = true;
                    break;
                }
            }
        } else {
            if (Route::is($routes)) {
                $active = true;
            }
        }

        if ($show) {
            return $active ? 'show' : '';
        }

        return $active ? $class : '';
    }
}
