<div class="page-header d-print-none" aria-label="Page header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle mb-2">{{ $pretitle }}</div>
                <h2 class="page-title">{{ $title }}</h2>
            </div>

            @if(!empty($buttons))
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        @foreach($buttons as $btn)
                            <a href="{{ $btn['url'] ?? '#' }}"
                               class="btn {{ $btn['class'] ?? 'btn-primary' }}"
                               {!! $btn['attrs'] ?? '' !!}>
                                @if(!empty($btn['icon']))
                                    <x-icon :name="$btn['icon']" class="me-1"/>
                                @endif
                                {{ $btn['text'] ?? '' }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
