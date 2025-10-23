<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Route;

class PageHeader extends Component
{
    public string $pretitle;
    public string $title;
    public array $buttons;

    /**
     * @param array $buttons Optional buttons array
     */
    public function __construct(array $buttons = [])
    {
        $this->buttons = $buttons;

        $routeName = Route::currentRouteName() ?? 'home';
        $segments = explode('.', $routeName);

        // Handle pretitle: could be multiple segments
        // Take all segments except the last one (which is usually action)
        if (count($segments) > 1) {
            $pretitleSegments = array_slice($segments, 0, -1);
        } else {
            $pretitleSegments = [$segments[0]];
        }

        $pretitleParts = [];
        foreach ($pretitleSegments as $seg) {
            $pretitleParts[] = __('titles.' . $seg) !== 'titles.' . $seg
                ? __('titles.' . $seg)
                : ucwords(str_replace(['-', '_'], ' ', $seg));
        }

        $this->pretitle = implode('/', $pretitleParts); // join with slash like example/example

        // Title: last segment is usually action
        $lastSegment = end($segments) ?: '';
        if (in_array($lastSegment, ['index', 'create', 'edit', 'show'])) {
            $action = __('titles.' . $lastSegment) !== 'titles.' . $lastSegment
                ? __('titles.' . $lastSegment)
                : ucwords(str_replace(['-', '_'], ' ', $lastSegment));

            // Combine action + last noun for natural language
            $lastNoun = end($pretitleParts) ?? '';
            $this->title = $action . ' ' . $lastNoun;
        } else {
            // Just show last segment or combined segments
            $this->title = __('titles.' . $lastSegment) !== 'titles.' . $lastSegment
                ? __('titles.' . $lastSegment)
                : ucwords(str_replace(['-', '_'], ' ', $lastSegment));
        }
    }

    public function render()
    {
        return view('components.page-header');
    }
}
