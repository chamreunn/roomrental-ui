<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\File;

class Icon extends Component
{
    public $name;
    public $class;
    public $width;
    public $height;

    public function __construct($name, $class = '', $width = 24, $height = 24)
    {
        $this->name = $name;
        $this->class = $class;
        $this->width = $width;
        $this->height = $height;
    }

    public function render()
    {
        // Path to Tabler icons in node_modules
        $path = base_path("node_modules/@tabler/icons/icons/outline/{$this->name}.svg");

        if (!File::exists($path)) {
            // fallback or error placeholder
            return <<<'blade'
                <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" stroke="red" />
                    <line x1="8" y1="8" x2="16" y2="16" stroke="red" />
                </svg>
            blade;
        }

        $svg = File::get($path);

        // Add custom attributes (width, height, classes)
        $svg = preg_replace(
            '/<svg /',
            '<svg class="' . e($this->class) . '" width="' . e($this->width) . '" height="' . e($this->height) . '" ',
            $svg,
            1
        );

        return $svg;
    }
}
