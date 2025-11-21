<?php

if (!function_exists('api_image')) {
    function api_image($path)
    {
        if (!$path) {
            return asset('imgs/default-avatar.png'); // fallback
        }

        // Base API
        $base = rtrim(apiBaseUrl(), '/');

        // Clean spaces and weird characters
        $clean = trim($path);

        // Normalize multiple spaces
        $clean = preg_replace('/\s+/', ' ', $clean);

        // Convert spaces to %20
        $clean = str_replace(' ', '%20', $clean);

        // Remove leading slash
        $clean = ltrim($clean, '/');

        return "{$base}/{$clean}";
    }
}
