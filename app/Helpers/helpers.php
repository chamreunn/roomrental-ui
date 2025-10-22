<?php

use Illuminate\Support\Facades\Session;

if (!function_exists('userRole')) {
    function userRole()
    {
        $user = Session::get('user');
        return $user['role'] ?? null;
    }
}


if (!function_exists('apiBaseUrl')) {
    function apiBaseUrl(): string
    {
        $host = request()->getHost();

        // If running locally (127.0.0.1 or localhost)
        if (in_array($host, ['127.0.0.1', 'localhost'])) {
            return env('API_LOCAL');
        }

        // Otherwise (production, staging, etc.)
        return env('API_HOSTING');
    }
}