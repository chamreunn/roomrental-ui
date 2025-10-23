<div class="empty-state text-center py-5">
    <!-- SVG illustration -->
    <img src="{{ asset($svg ?? 'svgs/no_result.svg') }}" width="{{ $width ?? '300px' }}" class="img-fluid"
        alt="{{ $title ?? __('messages.no_data') }}">

    <h3 class="mb-2">{{ $title ?? __('messages.no_data') }}</h3>
    <p class="text-muted mb-4">{{ $message ?? __('messages.no_data_message') }}</p>

    @if ($action)
        <a href="{{ $action['url'] ?? '#' }}" class="{{ $action['class'] ?? 'btn btn-primary' }}">
            @isset($action['icon'])
                <x-icon :name="$action['icon']" class="me-1" />
            @endisset
            {{ __($action['text'] ?? 'Take Action') }}
        </a>
    @endif
</div>
