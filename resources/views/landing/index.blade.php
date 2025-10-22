@extends('layouts.landing')

@section('title', __('landing.title'))

@section('content')

    {{-- 🏠 Hero Section with Carousel --}}
    <section id="home" class="position-relative">
        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-inner">
                @foreach ([['img' => 'https://picsum.photos/1920/800?random=1', 'title' => __('landing.hero.headline'), 'text' => __('landing.hero.subtext')], ['img' => 'https://picsum.photos/1920/800?random=2', 'title' => 'ស្វែងរកផ្ទះស្រួលៗជិតអ្នក', 'text' => 'ជ្រើសរើសផ្ទះស្រួលជាមួយ RoomioFlex'], ['img' => 'https://picsum.photos/1920/800?random=3', 'title' => 'បញ្ចេញការផ្សាយជួលផ្ទះរបស់អ្នក', 'text' => 'ជួយអ្នករកអ្នកជួលក្នុងរយៈពេលខ្លី!']] as $index => $slide)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ $slide['img'] }}" class="d-block w-100" alt="Hero Slide {{ $index + 1 }}">
                        <div
                            class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded-4 p-4 animate__animated animate__fadeInUp">
                            <h1 class="fw-bold text-white">{{ $slide['title'] }}</h1>
                            <p class="text-light">{{ $slide['text'] }}</p>
                            <a href="#rooms" class="btn btn-primary me-2">{{ __('landing.hero.start') }}</a>
                            <a href="#features" class="btn btn-outline-light">{{ __('landing.hero.learn') }}</a>
                        </div>
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <x-icon name="chevron-left" width="36" height="36" class="text-white" />
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <x-icon name="chevron-right" width="36" height="36" class="text-white" />
            </button>
        </div>
    </section>

    {{-- 🌟 Features --}}
    <section id="features" class="py-5 text-center">
        <div class="container">
            <h2 class="fw-bold mb-4">{{ __('landing.features.title') }}</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <x-icon name="map-pin-filled" class="text-primary mb-3" width="48" height="48" />
                    <h5>{{ __('landing.features.location') }}</h5>
                    <p class="text-muted">{{ __('landing.features.desc.location') }}</p>
                </div>
                <div class="col-md-4">
                    <x-icon name="shield-check-filled" class="text-success mb-3" width="48" height="48" />
                    <h5>{{ __('landing.features.safe') }}</h5>
                    <p class="text-muted">{{ __('landing.features.desc.safe') }}</p>
                </div>
                <div class="col-md-4">
                    <x-icon name="heart-filled" class="text-danger mb-3" width="48" height="48" />
                    <h5>{{ __('landing.features.favorite') }}</h5>
                    <p class="text-muted">{{ __('landing.features.desc.favorite') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 🏘 Rooms --}}
    <section id="rooms" class="bg-light py-5">
        <div class="container text-center">
            <h2 class="fw-bold mb-4">{{ __('landing.nav.rooms') }}</h2>
            <div class="row g-4">
                @foreach (range(1, 3) as $i)
                    <div class="col-md-4">
                        <div class="card shadow-sm rounded-4 overflow-hidden h-100">
                            <img src="https://picsum.photos/400/250?random={{ $i }}" class="card-img-top"
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

    {{-- 🎉 Promotions --}}
    <section id="promotions" class="py-5 text-center bg-white">
        <div class="container">
            <h2 class="fw-bold mb-4">
                <x-icon name="discount-2-filled" class="text-warning me-2" width="36" height="36" />
                Latest Promotions
            </h2>
            <p class="text-muted mb-5">Save more with exclusive discounts and special offers available now!</p>
            <div class="row g-4 justify-content-center">
                @foreach ([['title' => '🔥 20% Off Monthly Rent', 'desc' => 'Book before end of October and enjoy 20% off your first month.', 'badge' => 'Limited Time'], ['title' => '🏠 Free Wi-Fi & Cleaning', 'desc' => 'All new tenants get free high-speed internet and weekly cleaning services.'], ['title' => '🎁 Referral Rewards', 'desc' => 'Invite a friend and both get $10 credit towards your next payment.']] as $promo)
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body text-start p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <x-icon name="star-filled" class="text-warning me-2" width="28"
                                        height="28" />
                                    <h5 class="mb-0 fw-semibold">{{ $promo['title'] }}</h5>
                                </div>
                                <p class="text-muted">{{ $promo['desc'] }}</p>
                                @if (isset($promo['badge']))
                                    <span
                                        class="badge bg-danger-subtle text-danger border border-danger px-3 py-1 rounded-pill">{{ $promo['badge'] }}</span>
                                @endif
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <a href="#" class="btn btn-outline-primary btn-sm mb-3">Learn More</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 👥 Careers Section --}}
    <section id="careers" class="py-5 text-center">
        <div class="container">
            <h2 class="fw-bold mb-4"><x-icon name="briefcase-filled" class="text-primary me-2" width="32"
                    height="32" />Join Our Team</h2>
            <p class="text-muted mb-5">We’re hiring passionate people to help shape the future of housing in Cambodia.</p>
            <div class="row justify-content-center g-4">
                @foreach ([['title' => 'Frontend Developer', 'icon' => 'code'], ['title' => 'Customer Support', 'icon' => 'headphones'], ['title' => 'Marketing Specialist', 'icon' => 'megaphone']] as $job)
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm rounded-4 p-4 text-start">
                            <x-icon name="{{ $job['icon'] }}-filled" class="text-primary mb-3" width="40"
                                height="40" />
                            <h5>{{ $job['title'] }}</h5>
                            <p class="text-muted">We’re looking for talented people who love innovation and teamwork.</p>
                            <a href="#" class="btn btn-outline-primary btn-sm mt-auto">Apply Now</a>
                        </div>
                    </div>
                @endforeach
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
