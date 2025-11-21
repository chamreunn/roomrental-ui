@extends('layouts.app')

@section('content')

    <div class="row g-3">
        <form id="settings">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="d-flex align-items-center text-primary mb-0">
                    <span class="avatar avatar-2 bg-primary-lt">
                        <x-icon name="brush" />
                    </span>
                    <span class="mx-2">{{ __('settings.personalize') }}</span>
                </h3>
            </div>

            <div class="card-body">
                <div class="row">

                    <!-- Theme Mode -->
                    <div class="col-lg-6 col-sm-12">
                        <label class="form-label">{{ __('settings.theme_mode') }}</label>
                        <p class="form-hint">{{ __('settings.theme_mode_hint') }}</p>

                        <label class="form-check">
                            <div class="form-selectgroup-item cursor-pointer">
                                <input type="radio" name="theme" value="light" class="form-check-input" checked />
                                <div class="form-check-label">{{ __('settings.light') }}</div>
                            </div>
                        </label>

                        <label class="form-check">
                            <div class="form-selectgroup-item cursor-pointer">
                                <input type="radio" name="theme" value="dark" class="form-check-input" />
                                <div class="form-check-label">{{ __('settings.dark') }}</div>
                            </div>
                        </label>
                    </div>

                    <!-- Theme Color -->
                    <div class="col-lg-6 col-sm-12">
                        <label class="form-label">{{ __('settings.color') }}</label>
                        <p class="form-hint">{{ __('settings.color_hint') }}</p>
                        <div class="row g-2">
                            @foreach (['blue', 'azure', 'indigo', 'purple', 'pink', 'red', 'orange', 'yellow', 'lime', 'green', 'teal', 'cyan'] as $color)
                                <div class="col-auto">
                                    <label class="form-colorinput">
                                        <input name="theme-primary" type="radio" value="{{ $color }}" class="form-colorinput-input" />
                                        <span class="form-colorinput-color bg-{{ $color }}"></span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <!-- Font -->
                    <div class="col-lg-4 col-sm-12">
                        <label class="form-label">{{ __('settings.font') }}</label>
                        <p class="form-hint">{{ __('settings.font_hint') }}</p>
                        @foreach ([
                            'khmer' => 'Khmer OS Siemreap',
                            'khmer-mef' => 'Khmer MEF',
                            'battambang' => 'Battambang',
                            'noto-sans-khmer' => 'Noto Sans Khmer',
                            'koulen' => 'Koulen',
                            'freehand' => 'Freehand'
                        ] as $value => $label)
                            <label class="form-check">
                                <div class="form-selectgroup-item cursor-pointer">
                                    <input type="radio" name="theme-font" value="{{ $value }}" class="form-check-input" />
                                    <div class="form-check-label">{{ $label }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <!-- Base theme -->
                    <div class="col-lg-4 col-sm-12">
                        <label class="form-label">{{ __('settings.theme_base') }}</label>
                        <p class="form-hint">{{ __('settings.theme_base_hint') }}</p>
                        @foreach (['slate', 'gray', 'zinc', 'neutral', 'stone'] as $value)
                            <label class="form-check">
                                <div class="form-selectgroup-item cursor-pointer">
                                    <input type="radio" name="theme-base" value="{{ $value }}" class="form-check-input" {{ $value == 'gray' ? 'checked' : '' }} />
                                    <div class="form-check-label">{{ ucfirst($value) }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <!-- Corner radius -->
                    <div class="col-lg-4 col-sm-12">
                        <label class="form-label">{{ __('settings.corner_radius') }}</label>
                        <p class="form-hint">{{ __('settings.corner_radius_hint') }}</p>
                        @foreach ([0, 0.5, 1, 1.5, 2] as $radius)
                            <label class="form-check">
                                <div class="form-selectgroup-item cursor-pointer">
                                    <input type="radio" name="theme-radius" value="{{ $radius }}" class="form-check-input" {{ $radius == 1 ? 'checked' : '' }} />
                                    <div class="form-check-label">{{ $radius }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <!-- Font Size -->
                    <div class="col-lg-4 col-sm-12 d-grid">
                        <label class="form-label">{{ __('settings.font_size') }}</label>
                        <input type="range" min="12" max="32" step="1" name="theme-font-size" class="form-range">
                        <span class="range-value" id="font-size-value"></span>
                        <span class="form-hint">{{ __('settings.font_size_hint') }}</span>
                    </div>

                    <!-- Line Height -->
                    <div class="col-lg-4 col-sm-12 d-grid">
                        <label class="form-label">{{ __('settings.line_height') }}</label>
                        <input type="range" min="1" max="2" step="0.05" name="theme-line-height" class="form-range">
                        <span class="range-value" id="line-height-value"></span>
                        <span class="form-hint">{{ __('settings.line_height_hint') }}</span>
                    </div>

                    <!-- Language -->
                    <div class="col-lg-4 col-sm-12">
                        <label class="form-label">{{ __('settings.language') }}</label>
                        <p class="form-hint">{{ __('settings.language_hint') }}</p>
                        <div>
                            <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown">
                                @if (app()->getLocale() == 'en')
                                    <span class="flag flag-country-us me-2" style="width: 20px; height: 18px;"></span>
                                    English
                                @else
                                    <span class="flag flag-country-kh me-2" style="width: 20px; height: 18px;"></span>
                                    ខ្មែរ
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                <a class="dropdown-item d-flex align-items-center" href="{{ url('lang/en') }}">
                                    <span class="flag flag-country-us me-2" style="width: 20px; height: 18px;"></span> English
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="{{ url('lang/km') }}">
                                    <span class="flag flag-country-kh me-2" style="width: 20px; height: 18px;"></span> ខ្មែរ
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-footer">
                <button id="reset-changes" class="btn btn-primary" type="button">
                    {{ __('settings.reset') }}
                </button>
            </div>
        </div>
    </div>
</form>

    </div>

@endsection

