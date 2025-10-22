@extends('layouts.landing')

@section('title', __('landing.title'))

@section('content')

    {{-- 🌐 Navbar --}}
    <header class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href=".">RoomioFlex</a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <x-icon name="menu-2" width="24" height="24" />
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                    <li class="nav-item"><a class="nav-link active" href="#home">{{ __('landing.nav.home') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">{{ __('landing.nav.features') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#rooms">{{ __('landing.nav.rooms') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#aboutus">{{ __('landing.nav.aboutus') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">{{ __('landing.nav.contact') }}</a></li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-lg-3" href="{{ route('login') }}">{{ __('landing.nav.login') }}</a>
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
                                    <span class="flag flag-country-us"
                                        style="width: 20px; height: 18px;"></span> English
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="{{ url('lang/km') }}">
                                    <span class="flag flag-country-kh"
                                        style="width: 20px; height: 18px;"></span> ខ្មែរ
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    {{-- 🏠 Hero Section --}}
    <section id="home" class="py-5 bg-light text-center">
        <div class="container py-5">
            <h1 class="display-5 fw-bold mb-3">{{ __('landing.hero.headline') }}</h1>
            <p class="lead mb-4 text-muted">{{ __('landing.hero.subtext') }}</p>
            <a href="#rooms" class="btn btn-primary btn-lg me-2">{{ __('landing.hero.start') }}</a>
            <a href="#features" class="btn btn-outline-secondary btn-lg">{{ __('landing.hero.learn') }}</a>
        </div>
    </section>

    {{-- 🌟 Features --}}
    <section id="features" class="py-5">
        <div class="container text-center">
            <h2 class="fw-bold mb-4">{{ __('landing.features.title') }}</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <x-icon name="map-pin" class="text-primary mb-3" width="48" height="48" />
                    <h5>{{ __('landing.features.location') }}</h5>
                    <p class="text-muted">{{ __('landing.features.desc.location') }}</p>
                </div>
                <div class="col-md-4">
                    <x-icon name="shield-check" class="text-success mb-3" width="48" height="48" />
                    <h5>{{ __('landing.features.safe') }}</h5>
                    <p class="text-muted">{{ __('landing.features.desc.safe') }}</p>
                </div>
                <div class="col-md-4">
                    <x-icon name="heart" class="text-danger mb-3" width="48" height="48" />
                    <h5>{{ __('landing.features.favorite') }}</h5>
                    <p class="text-muted">{{ __('landing.features.desc.favorite') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 🏘 Room Preview --}}
    <section id="rooms" class="bg-light py-5">
        <div class="container text-center">
            <h2 class="fw-bold mb-4">{{ __('landing.nav.rooms') }}</h2>
            <div class="row g-4">
                @foreach (range(1, 3) as $i)
                    <div class="col-md-4">
                        <div class="card shadow-sm rounded-4">
                            <img src="https://picsum.photos/400/250?random={{ $i }}" class="card-img-top rounded-top-4"
                                alt="Room">
                            <div class="card-body">
                                <h5 class="card-title">Room #{{ $i }}</h5>
                                <p class="card-text text-muted">គ្រឿងសង្ហារិមពេញលេញ ជិតផ្សារ និងសាលា។</p>
                                <button class="btn btn-outline-primary btn-sm">{{ __('landing.hero.start') }}</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 🧭 How It Works --}}
    <section id="aboutus" class="py-5 text-center">
        <div class="container">
            <h2 class="fw-bold mb-4">{{ __('landing.how.title') }}</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <x-icon name="search" class="text-info mb-3" width="48" height="48" />
                    <p>{{ __('landing.how.steps.search') }}</p>
                </div>
                <div class="col-md-4">
                    <x-icon name="phone" class="text-warning mb-3" width="48" height="48" />
                    <p>{{ __('landing.how.steps.contact') }}</p>
                </div>
                <div class="col-md-4">
                    <x-icon name="check" class="text-success mb-3" width="48" height="48" />
                    <p>{{ __('landing.how.steps.book') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 📞 Contact --}}
    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <h2 class="fw-bold text-center mb-4">{{ __('landing.contact.title') }}</h2>
            <form class="mx-auto" style="max-width: 600px;">
                <div class="mb-3">
                    <label class="form-label">{{ __('landing.contact.name') }}</label>
                    <input type="text" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('landing.contact.email') }}</label>
                    <input type="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('landing.contact.message') }}</label>
                    <textarea class="form-control" rows="4"></textarea>
                </div>
                <button class="btn btn-primary">{{ __('landing.contact.send') }}</button>
            </form>
        </div>
    </section>

    {{-- ⚓ Footer --}}
    <footer class="py-4 bg-dark text-white text-center">
        <p class="mb-0">{{ __('landing.footer') }}</p>
    </footer>

@endsection
