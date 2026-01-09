<?php

if (!function_exists('api_image')) {
    function api_image($path = null)
    {
        if (empty($path)) {
            return asset('imgs/default-avatar.png');
        }

        $base = rtrim(apiBaseUrl(), '/');
        $clean = trim($path);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = str_replace(' ', '%20', $clean);
        $clean = ltrim($clean, '/');

        return "{$base}/{$clean}";
    }
}
