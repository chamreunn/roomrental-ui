<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Icon extends Component
{
    public $name;
    public $class;

    /**
     * Create a new component instance.
     *
     * @param string $name
     * @param string $class
     */
    public function __construct(string $name, string $class = '')
    {
        $this->name = $name;
        $this->class = $class;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.icon');
    }
}
