<header class="navbar navbar-expand-md d-none d-lg-flex d-print-none sticky-top">
    <div class="container-xl">
        <!-- BEGIN NAVBAR TOGGLER -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu"
            aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- END NAVBAR TOGGLER -->
        <!-- BEGIN NAVBAR LOGO -->

        <div class="navbar-brand navbar-brand-autodark fw-bold text-primary">
            @if(userRole() == 'user')
                <a href="{{ dashboardRoute() }}" aria-label="Tabler">
                    <img src="{{ asset('favicon.ico') }}" width="40" alt="">RoomioFlex
                </a>
            @endif
        </div>

        <!-- END NAVBAR LOGO -->
        <div class="navbar-nav flex-row order-md-last d-none-navbar-horizontal">
            <div class="d-none d-md-flex">
                <!-- Theme Switch -->
                <div class="nav-item">
                    <a href="?theme=dark" class="nav-link px-0 hide-theme-dark" data-bs-toggle="tooltip"
                        data-bs-placement="bottom" aria-label="{{ __('nav.dark_mode') }}"
                        data-bs-original-title="{{ __('nav.dark_mode') }}">
                        <x-icon name="moon" />
                    </a>
                    <a href="?theme=light" class="nav-link px-0 hide-theme-light" data-bs-toggle="tooltip"
                        data-bs-placement="bottom" aria-label="{{ __('nav.light_mode') }}"
                        data-bs-original-title="{{ __('nav.light_mode') }}">
                        <x-icon name="sun" />
                    </a>
                </div>
                <!-- Settings -->
                <div class="nav-item btn-animate-icon btn-animate-icon-rotate">
                    <a href="{{ route('settings.index') }}" class="nav-link px-0 {{ active_class('settings.index') }}"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" aria-label="{{ __('nav.settings') }}"
                        data-bs-original-title="{{ __('nav.settings') }}">
                        <x-icon name="settings" />
                    </a>
                </div>
            </div>

            {{-- Language Switch --}}
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown">
                    @if (app()->getLocale() == 'en')
                        <span class="flag flag-country-us me-2" style="width: 20px; height: 18px;"></span> English
                    @else
                        <span class="flag flag-country-kh me-2" style="width: 20px; height: 18px;"></span> ខ្មែរ
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

            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown">
                    <span class="avatar avatar-sm"
                        style="background-image: url('{{ $authUser['profile_picture'] }}')"></span>
                    <div class="d-none d-xl-block ps-2">
                        <div class="text-uppercase fw-bolder text-primary">{{ $authUser['name'] }}</div>
                        <div class="mt-1 small text-secondary">{{ ucfirst($authUser['role']) }}</div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="{{ route('settings.index') }}" class="dropdown-item">
                        <x-icon name="settings" class="me-1" />
                        {{ __('nav.settings') }}
                    </a>
                    <a href="{{ route('logout') }}" class="dropdown-item text-danger">
                        <x-icon name="logout-2" class="me-1" />
                        {{ __('nav.logout') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
