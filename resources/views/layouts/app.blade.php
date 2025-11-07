<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ page_title() }}</title>

    <!-- Vite -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js','resources/js/settings.js'])
</head>

<body>
    <!-- ✅ Page Loader -->
    <div id="page-loader"
        class="page page-center position-fixed top-0 start-0 w-100 h-100 bg-body z-9999 d-flex align-items-center justify-content-center">
        <div class="container container-slim py-4">
            <div class="text-center">
                <div class="mb-3">
                    <a href="." class="navbar-brand navbar-brand-autodark">
                        <img src="{{ asset('favicon.ico') }}" width="100" height="16" alt="Logo">
                    </a>
                </div>
                <div class="text-secondary mb-3">{{ __('landing.loader') }}</div>
                <div class="progress progress-sm">
                    <div class="progress-bar progress-bar-indeterminate"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Main App Content (Hidden until loaded) -->
    <div id="app-content" style="display: none;">
        <div class="page">
            @if(userRole() != 'user')
                @include('partials.sidebar')
            @endif
            @include('partials.nav')

            <div class="page-wrapper">
                <x-page-header :buttons="$buttons ?? []" />

                <div class="page-body">
                    <div class="container-xl">
                        <x-alert />
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- ✅ Loader JS -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const loader = document.getElementById('page-loader');
            const content = document.getElementById('app-content');

            window.addEventListener('load', function () {
                loader.style.transition = 'opacity 0.4s ease';
                loader.style.opacity = 0;
                setTimeout(() => {
                    loader.style.display = 'none';
                    content.style.display = 'block';
                }, 400);
            });
        });
    </script>

    <!-- ✅ Optional: AJAX Loader (for dynamic requests) -->
    <script>
        if (typeof axios !== 'undefined') {
            axios.interceptors.request.use(config => {
                document.getElementById('page-loader').style.display = 'flex';
                return config;
            }, error => Promise.reject(error));

            axios.interceptors.response.use(response => {
                setTimeout(() => document.getElementById('page-loader').style.display = 'none', 300);
                return response;
            }, error => {
                document.getElementById('page-loader').style.display = 'none';
                return Promise.reject(error);
            });
        }
    </script>

    {{-- for calendar flatpickr --}}
    @php
        $months = [
            'en' => [
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December',
            ],
            'km' => [
                'មករា',
                'កុម្ភៈ',
                'មិនា',
                'មេសា',
                'ឧសភា',
                'មិថុនា',
                'កក្កដា',
                'សីហា',
                'កញ្ញា',
                'តុលា',
                'វិច្ឆិកា',
                'ធ្នូ',
            ],
        ];
    @endphp

    <script>
        window.appLocale = "{{ app()->getLocale() }}";
        window.monthsTranslation = @json($months);
    </script>

</body>

</html>
