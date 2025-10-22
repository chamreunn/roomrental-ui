<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Welcome')</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
    {{-- 🌐 Navbar --}}
    <header class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="."><span><img src="{{ asset('favicon.ico') }}" width="30"
                        class="border rounded shadow-lg" alt="RoomioFlex"></span>RoomioFlex</a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <x-icon name="menu-2" width="24" height="24" />
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                    <li class="nav-item">
                        <a class="btn btn-primary ms-lg-3" href=".">{{ __('landing.nav.home') }}</a>
                    </li>
                    {{-- Language Switch --}}
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link d-flex align-items-center" data-bs-toggle="dropdown" href="#">
                            @if(app()->getLocale() == 'en')
                                <span class="flag flag-country-us me-2" style="width: 20px; height: 18px;"></span>
                                English
                            @else
                                <span class="flag flag-country-kh me-2" style="width: 20px; height: 18px;"></span>
                                ខ្មែរ
                            @endif
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="{{ url('lang/en') }}">
                                    <span class="flag flag-country-us" style="width: 20px; height: 18px;"></span>
                                    English
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="{{ url('lang/km') }}">
                                    <span class="flag flag-country-kh" style="width: 20px; height: 18px;"></span> ខ្មែរ
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div class="row g-0 flex-fill">
        @yield('content')
    </div>

</body>

</html>
