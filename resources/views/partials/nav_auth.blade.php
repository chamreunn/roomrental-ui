{{-- 🌐 Navbar --}}
<header class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href=".">
            <img src="{{ asset('favicon.ico') }}" width="30" alt="RoomioFlex">RoomioFlex
        </a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <x-icon name="menu-2" width="24" height="24" />
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item btn-animate-icon btn-animate-icon-tada">
                    <a href="?theme=dark" class="nav-link px-0 hide-theme-dark" data-bs-toggle="tooltip"
                        data-bs-placement="bottom" aria-label="Enable dark mode"
                        data-bs-original-title="Enable dark mode">
                        <x-icon name="moon" />
                    </a>
                    <a href="?theme=light" class="nav-link px-0 hide-theme-light" data-bs-toggle="tooltip"
                        data-bs-placement="bottom" aria-label="Enable light mode"
                        data-bs-original-title="Enable light mode">
                        <x-icon name="sun" />
                    </a>
                </li>
                {{-- Language Switch --}}
                <li class="nav-item dropdown ms-3">
                    <a class="nav-link d-flex align-items-center" data-bs-toggle="dropdown" href="#">
                        @if (app()->getLocale() == 'en')
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
                <li class="nav-item">
                    <a class="btn btn-primary ms-lg-3" href=".">{{ __('landing.nav.home') }}</a>
                </li>
            </ul>
        </div>
    </div>
</header>
