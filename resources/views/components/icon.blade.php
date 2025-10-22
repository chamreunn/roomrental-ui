@props(['name', 'class' => ''])

@php
$iconsPath = base_path("node_modules/@tabler/icons/icons/outline/{$name}.svg");

// Read the SVG file
$svg = file_exists($iconsPath) ? file_get_contents($iconsPath) : '';

// Inject the class into the <svg> tag
if ($svg) {
    $svg = preg_replace('/<svg /', '<svg class="'.$class.'" ', $svg, 1);
}
@endphp

{!! $svg !!}
