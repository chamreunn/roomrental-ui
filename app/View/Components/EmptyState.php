<?php

namespace App\View\Components;

use Illuminate\View\Component;

class EmptyState extends Component
{
    public $title;
    public $message;
    public $action;
    public $svg;
    public $width;

    /**
     * Create a new component instance.
     */
    public function __construct($title = null, $message = null, $action = null, $svg = null, $width = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->action = $action;
        $this->svg = $svg;
        $this->width = $width;
    }

    public function render()
    {
        return view('components.empty-state');
    }
}
