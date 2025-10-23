@props(['name', 'class' => ''])

@php
$iconsPath = base_path("node_modules/@tabler/icons/icons/outline/{$name}.svg");

$svg = '';

if (file_exists($iconsPath)) {
    $svg = file_get_contents($iconsPath);

    // If the SVG already has a class, append it, otherwise add it
    if (preg_match('/<svg[^>]*class="([^"]*)"/', $svg, $matches)) {
        $existingClass = $matches[1];
        $newClass = trim($existingClass . ' ' . $class);
        $svg = preg_replace('/(<svg[^>]*class=")[^"]*"/', '${1}' . $newClass . '"', $svg);
    } else {
        // Insert class after <svg
        $svg = preg_replace('/<svg /', '<svg class="'.$class.'" ', $svg, 1);
    }
}
@endphp

{!! $svg !!}
