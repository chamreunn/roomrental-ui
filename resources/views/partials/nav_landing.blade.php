{{-- 🌐 Navbar --}}
<header class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href=".">RoomioFlex</a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <x-icon name="menu-2" class="text-dark" width="24" height="24" />
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item"><a class="nav-link active" href="#home">{{ __('landing.nav.home') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="#features">{{ __('landing.nav.features') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="#rooms">{{ __('landing.nav.rooms') }}</a></li>
                {{-- <li class="nav-item"><a class="nav-link" href="#promotions">Promotions</a></li> --}}
                <li class="nav-item"><a class="nav-link" href="#aboutus">{{ __('landing.nav.aboutus') }}</a></li>
                {{-- <li class="nav-item"><a class="nav-link"
                        href="#careers">{{ __('landing.nav.careers') ?? 'Careers' }}</a></li> --}}
                <li class="nav-item"><a class="nav-link" href="#contact">{{ __('landing.nav.contact') }}</a></li>
                <li class="nav-item">
                    <a class="btn btn-primary ms-lg-3" href="{{ route('login') }}">{{ __('landing.nav.login') }}</a>
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
                                <span class="flag flag-country-us" style="width: 20px; height: 18px;"></span> English
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
