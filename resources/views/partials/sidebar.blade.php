<aside class="navbar navbar-vertical navbar-expand-lg d-print-none" data-bs-theme="dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
            aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-nav flex-row d-lg-none">
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
                <div class="nav-item dropdown d-none d-md-flex">
                    <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1"
                        aria-label="Show notifications" data-bs-auto-close="outside" aria-expanded="false">
                        <!-- Download SVG icon from http://tabler.io/icons/icon/bell -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="icon icon-1">
                            <path
                                d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6">
                            </path>
                            <path d="M9 17v1a3 3 0 0 0 6 0v-1"></path>
                        </svg>
                        <span class="badge bg-red"></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">
                        <div class="card">
                            <div class="card-header d-flex">
                                <h3 class="card-title">Notifications</h3>
                                <div class="btn-close ms-auto" data-bs-dismiss="dropdown"></div>
                            </div>
                            <div class="list-group list-group-flush list-group-hoverable">
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-auto"><span
                                                class="status-dot status-dot-animated bg-red d-block"></span></div>
                                        <div class="col text-truncate">
                                            <a href="#" class="text-body d-block">Example 1</a>
                                            <div class="d-block text-secondary text-truncate mt-n1">Change deprecated
                                                html tags to text decoration classes (#29604)</div>
                                        </div>
                                        <div class="col-auto">
                                            <a href="#" class="list-group-item-actions">
                                                <!-- Download SVG icon from http://tabler.io/icons/icon/star -->
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon text-muted icon-2">
                                                    <path
                                                        d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z">
                                                    </path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-auto"><span class="status-dot d-block"></span></div>
                                        <div class="col text-truncate">
                                            <a href="#" class="text-body d-block">Example 2</a>
                                            <div class="d-block text-secondary text-truncate mt-n1">
                                                justify-content:between ⇒ justify-content:space-between (#29734)</div>
                                        </div>
                                        <div class="col-auto">
                                            <a href="#" class="list-group-item-actions show">
                                                <!-- Download SVG icon from http://tabler.io/icons/icon/star -->
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon text-yellow icon-2">
                                                    <path
                                                        d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z">
                                                    </path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-auto"><span class="status-dot d-block"></span></div>
                                        <div class="col text-truncate">
                                            <a href="#" class="text-body d-block">Example 3</a>
                                            <div class="d-block text-secondary text-truncate mt-n1">Update
                                                change-version.js (#29736)</div>
                                        </div>
                                        <div class="col-auto">
                                            <a href="#" class="list-group-item-actions">
                                                <!-- Download SVG icon from http://tabler.io/icons/icon/star -->
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon text-muted icon-2">
                                                    <path
                                                        d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z">
                                                    </path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-auto"><span
                                                class="status-dot status-dot-animated bg-green d-block"></span></div>
                                        <div class="col text-truncate">
                                            <a href="#" class="text-body d-block">Example 4</a>
                                            <div class="d-block text-secondary text-truncate mt-n1">Regenerate
                                                package-lock.json (#29730)</div>
                                        </div>
                                        <div class="col-auto">
                                            <a href="#" class="list-group-item-actions">
                                                <!-- Download SVG icon from http://tabler.io/icons/icon/star -->
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon text-muted icon-2">
                                                    <path
                                                        d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z">
                                                    </path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <a href="#" class="btn btn-2 w-100"> Archive all </a>
                                    </div>
                                    <div class="col">
                                        <a href="#" class="btn btn-2 w-100"> Mark all as read </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="nav-item btn-animate-icon btn-animate-icon-rotate">
                    <a href="#" class="nav-link px-0" data-bs-toggle="tooltip" data-bs-placement="bottom"
                        aria-label="{{ __('nav.settings') }}" data-bs-original-title="{{ __('nav.settings') }}">
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
                        style="background-image: url({{ asset($authUser['profile_picture']) }})"></span>
                    <div class="d-none d-xl-block ps-2">
                        <div class="text-uppercase fw-bolder text-primary">{{ $authUser['name'] }}</div>
                        <div class="mt-1 small text-secondary">{{ $authUser['role'] }}</div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="#" class="dropdown-item">{{ __('nav.status') }}</a>
                    <a href="#" class="dropdown-item">{{ __('nav.profile') }}</a>
                    <a href="#" class="dropdown-item">{{ __('nav.feedback') }}</a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">{{ __('nav.settings') }}</a>
                    <a href="{{ route('logout') }}" class="dropdown-item">{{ __('nav.logout') }}</a>
                </div>
            </div>
        </div>

        <div class="collapse navbar-collapse" id="sidebar-menu">
            <div class="navbar-brand navbar-brand-autodark fw-bold text-primary">
                <a href="{{ dashboardRoute() }}" aria-label="Tabler">
                    <img src="{{ asset('favicon.ico') }}" width="40" alt="">RoomioFlex
                </a>
            </div>

            <ul class="navbar-nav">

                <!-- Dashboard -->
                <li class="nav-item {{ active_class('dashboard.*', 'active') }}">
                    <a class="nav-link btn-animate-icon btn-animate-icon-move-start" href="{{ dashboardRoute() }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <x-icon name="home" />
                        </span>
                        <span class="nav-link-title mb-0">{{ __('sidebar.home') }}</span>
                    </a>
                </li>

                <!-- Accounts -->
                <li class="nav-item dropdown {{ active_class('account.*') }}">
                    <a class="nav-link dropdown-toggle btn-animate-icon btn-animate-icon-move-start {{ active_class('account.*') }}"
                        href="#navbar-account" data-bs-toggle="dropdown" data-bs-auto-close="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <x-icon name="user" />
                        </span>
                        <span class="nav-link-title">{{ __('sidebar.account') }}</span>
                    </a>
                    <div class="dropdown-menu {{ active_class('account.*', 'active', true) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ active_class('account.create') }}"
                                    href="{{ route('account.create') }}">
                                    {{ __('sidebar.create_account') }}
                                </a>
                                <a class="dropdown-item {{ active_class('account.index') }}"
                                    href="{{ route('account.index') }}">
                                    {{ __('sidebar.account_list') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Location -->
                <li class="nav-item dropdown {{ active_class(['location.*','user_location.*']) }}">
                    <a class="nav-link dropdown-toggle btn-animate-icon btn-animate-icon-move-start {{ active_class(['location.*','user_location.*']) }}"
                        href="#navbar-location" data-bs-toggle="dropdown" data-bs-auto-close="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <x-icon name="map-pin" />
                        </span>
                        <span class="nav-link-title">{{ __('sidebar.location') }}</span>
                    </a>
                    <div class="dropdown-menu {{ active_class(['location.*','user_location.*'], '', true) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ active_class('location.create') }}"
                                    href="{{ route('location.create') }}">
                                    {{ __('sidebar.location_created') }}
                                </a>
                                <a class="dropdown-item {{ active_class('location.index') }}"
                                    href="{{ route('location.index') }}">
                                    {{ __('sidebar.location_list') }}
                                </a>
                                <a class="dropdown-item {{ active_class('user_location.index') }}"
                                    href="{{ route('user_location.index') }}">
                                    {{ __('user_location.user_location') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Room Type -->
                <li class="nav-item dropdown {{ active_class(['roomtype.*']) }}">
                    <a class="nav-link dropdown-toggle btn-animate-icon btn-animate-icon-move-start {{ active_class(['roomtype.*']) }}"
                        href="#navbar-room-type" data-bs-toggle="dropdown" data-bs-auto-close="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <x-icon name="tag" />
                        </span>
                        <span class="nav-link-title">{{ __('sidebar.room_type') }}</span>
                    </a>
                    <div class="dropdown-menu {{ active_class(['roomtype.*'], '', true) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ active_class('roomtype.create') }}"
                                    href="{{ route('roomtype.create') }}">
                                    {{ __('sidebar.create_room_type') }}
                                </a>
                                <a class="dropdown-item {{ active_class('roomtype.index') }}"
                                    href="{{ route('roomtype.index') }}">
                                    {{ __('sidebar.room_type_list') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Room -->
                <li class="nav-item dropdown {{ active_class(['room.*']) }}">
                    <a class="nav-link dropdown-toggle btn-animate-icon btn-animate-icon-move-start {{ active_class(['room.*']) }}"
                        href="#navbar-room" data-bs-toggle="dropdown" data-bs-auto-close="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <x-icon name="door" />
                        </span>
                        <span class="nav-link-title">{{ __('sidebar.room') }}</span>
                    </a>
                    <div class="dropdown-menu {{ active_class(['room.*'], '', true) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ active_class(['room.choose_location','room.create_room']) }}"
                                    href="{{ route('room.choose_location') }}">
                                    {{ __('sidebar.create_room') }}
                                </a>
                                <a class="dropdown-item {{ active_class(['room.index','room.room_list','room.show']) }}"
                                    href="{{ route('room.index') }}">
                                    {{ __('sidebar.room_list') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Clients -->
                <li class="nav-item {{ active_class('clients.*', 'active') }}">
                    <a class="nav-link {{ active_class('clients.*') }}" href="{{ route('clients.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <x-icon name="users" />
                        </span>
                        <span class="nav-link-title">{{ __('sidebar.client') }}</span>
                    </a>
                </li>

                <!-- Invoice -->
                <li class="nav-item dropdown {{ active_class(['invoice.*']) }}">
                    <a class="nav-link dropdown-toggle btn-animate-icon btn-animate-icon-move-start {{ active_class(['invoice.*']) }}"
                        href="#navbar-invoice" data-bs-toggle="dropdown" data-bs-auto-close="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <x-icon name="invoice" />
                        </span>
                        <span class="nav-link-title">{{ __('sidebar.invoice') }}</span>
                    </a>
                    <div class="dropdown-menu {{ active_class(['invoice.*'], '', true) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ active_class(['invoice.choose_location','invoice.choose_room']) }}"
                                    href="{{ route('invoice.choose_location') }}">
                                    {{ __('sidebar.create_invoice') }}
                                </a>
                                <a class="dropdown-item {{ active_class(['invoice.index','invoice.invoice_list','invoice.show']) }}"
                                    href="{{ route('invoice.index') }}">
                                    {{ __('sidebar.invoice_list') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Income -->
                <li class="nav-item">
                    <a class="nav-link {{ active_class('income.*') }}" href="{{ url('income') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <x-icon name="cash-register" />
                        </span>
                        <span class="nav-link-title">{{ __('sidebar.income') }}</span>
                    </a>
                </li>

                <!-- Expense -->
                <li class="nav-item">
                    <a class="nav-link {{ active_class('expense.*') }}" href="{{ url('expense') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <x-icon name="businessplan" />
                        </span>
                        <span class="nav-link-title">{{ __('sidebar.expense') }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>
