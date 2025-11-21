@props(['name', 'class' => ''])

@php
    $iconsPath = public_path("icons/{$name}.svg");

    $svg = '';

    if (file_exists($iconsPath)) {
        $svg = file_get_contents($iconsPath);

        // Add class
        if (preg_match('/<svg[^>]*class="([^"]*)"/', $svg, $matches)) {
            $existingClass = $matches[1];
            $newClass = trim($existingClass . ' ' . $class);
            $svg = preg_replace('/(<svg[^>]*class=")[^"]*"/', '${1}' . $newClass . '"', $svg);
        } else {
            $svg = preg_replace('/<svg /', '<svg class="' . $class . '" ', $svg, 1);
        }
    }
@endphp

{!! $svg !!}
