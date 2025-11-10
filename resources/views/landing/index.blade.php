@extends('layouts.landing')

@section('title', __('landing.title'))

@section('content')

    {{-- 🏠 Hero Section with Carousel --}}
    <section id="home" class="position-relative">
        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="6000">
            <div class="carousel-inner">
                @php
                    $slides = [
                        [
                            'img' => 'https://www.thespruce.com/thmb/iMt63n8NGCojUETr6-T8oj-5-ns=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/PAinteriors-7-cafe9c2bd6be4823b9345e591e4f367f.jpg',
                            'title' => __('landing.hero.headline'),
                            'text' => __('landing.hero.subtext'),
                        ],
                        [
                            'img' => 'https://www.vanorohotel.com/wp-content/uploads/2021/07/drz-vanoro_6737.jpg',
                            'title' => 'ស្វែងរកផ្ទះស្រួលៗជិតអ្នក',
                            'text' => 'ជ្រើសរើសផ្ទះស្រួលជាមួយ RoomioFlex',
                        ],
                        [
                            'img' => 'https://www.houzlook.com/assets/images/upload/Rooms/Bed%20Rooms/Malson%20Modern%20Bed%20Room-20180819090641741.jpg',
                            'title' => 'បញ្ចេញការផ្សាយជួលផ្ទះរបស់អ្នក',
                            'text' => 'ជួយអ្នករកអ្នកជួលក្នុងរយៈពេលខ្លី!',
                        ],
                    ];
                @endphp

                @foreach ($slides as $index => $slide)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ $slide['img'] }}" class="d-block w-100"
                            style="object-fit: cover; width: 100%; height: 800px;" alt="Slide {{ $index + 1 }}">
                        <div class="carousel-caption bg-dark bg-opacity-50 rounded-4 p-4" data-aos="fade-up"
                            data-aos-duration="1200" data-aos-easing="ease-in-out">
                            <h1 class="fw-bold text-white display-4">{{ $slide['title'] }}</h1>
                            <p class="text-light lead">{{ $slide['text'] }}</p>
                            <div class="mt-3">
                                <a href="#rooms" class="btn btn-primary me-2 px-4 py-2">{{ __('landing.hero.start') }}</a>
                                <a href="#features" class="btn btn-outline-light px-4 py-2">{{ __('landing.hero.learn') }}</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Carousel Controls --}}
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <x-icon name="chevron-left" width="36" height="36" class="text-white" />
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <x-icon name="chevron-right" width="36" height="36" class="text-white" />
            </button>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="row items-center text-center g-lg-10">
                <div class="col-md-6 col-lg">
                    <div class="shape shape-md mb-3">
                        <!-- Download SVG icon from http://tabler.io/icons/icon/devices -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-1">
                            <path d="M13 9a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-6a1 1 0 0 1 -1 -1v-10z"></path>
                            <path d="M18 8v-3a1 1 0 0 0 -1 -1h-13a1 1 0 0 0 -1 1v12a1 1 0 0 0 1 1h9"></path>
                            <path d="M16 9h2"></path>
                        </svg>
                    </div>
                    <h2 class="h2">Mobile-optimized</h2>
                    <p class="text-secondary">Our email templates are fully responsive, so you can be sure they will look
                        great on any device and screen size.</p>
                </div>
                <div class="col-md-6 col-lg">
                    <div class="shape shape-md mb-3">
                        <!-- Download SVG icon from http://tabler.io/icons/icon/mailbox -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-1">
                            <path d="M10 21v-6.5a3.5 3.5 0 0 0 -7 0v6.5h18v-6a4 4 0 0 0 -4 -4h-10.5"></path>
                            <path d="M12 11v-8h4l2 2l-2 2h-4"></path>
                            <path d="M6 15h1"></path>
                        </svg>
                    </div>
                    <h2 class="h2">Compatible with 90+ email clients</h2>
                    <p class="text-secondary">
                        Tested across 90+ email clients and devices, Tabler emails will help you make your email
                        communication professional and reliable.
                    </p>
                </div>
                <div class="col-md-6 col-lg">
                    <div class="shape shape-md mb-3">
                        <!-- Download SVG icon from http://tabler.io/icons/icon/palette -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-1">
                            <path
                                d="M12 21a9 9 0 0 1 0 -18c4.97 0 9 3.582 9 8c0 1.06 -.474 2.078 -1.318 2.828c-.844 .75 -1.989 1.172 -3.182 1.172h-2.5a2 2 0 0 0 -1 3.75a1.3 1.3 0 0 1 -1 2.25">
                            </path>
                            <path d="M8.5 10.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                            <path d="M12.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                            <path d="M16.5 10.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                        </svg>
                    </div>
                    <h2 class="h2">Unique, minimal design</h2>
                    <p class="text-secondary">Draw recipients’ attention with beautiful, minimal email designs based on
                        Bootstrap and Material Design principles.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 🌟 Features --}}
    <section id="features" class="section-light mt-n12 pt-12">
        <div class="container">
            <h2 class="fw-bold mb-4" data-aos="fade-down" data-aos-duration="800">{{ __('landing.features.title') }}</h2>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-right" data-aos-duration="1000">
                    <x-icon name="map-pin-filled" class="text-primary mb-3" width="48" height="48" />
                    <h5>{{ __('landing.features.location') }}</h5>
                    <p class="text-muted">{{ __('landing.features.desc.location') }}</p>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-duration="1000">
                    <x-icon name="shield-check-filled" class="text-success mb-3" width="48" height="48" />
                    <h5>{{ __('landing.features.safe') }}</h5>
                    <p class="text-muted">{{ __('landing.features.desc.safe') }}</p>
                </div>
                <div class="col-md-4" data-aos="fade-left" data-aos-duration="1000">
                    <x-icon name="heart-filled" class="text-danger mb-3" width="48" height="48" />
                    <h5>{{ __('landing.features.favorite') }}</h5>
                    <p class="text-muted">{{ __('landing.features.desc.favorite') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 🏘 Rooms --}}
    <section id="rooms" class="section py-5">
        <div class="container text-center">
            <h2 class="fw-bold mb-5" data-aos="fade-up" data-aos-duration="800">{{ __('landing.nav.rooms') }}</h2>
            <div class="row g-4">
                @foreach (range(1, 3) as $i)
                    <div class="col-md-4" data-aos="flip-up" data-aos-duration="{{ 1000 + $i * 200 }}">
                        <div class="card shadow-lg border-0 rounded-4 overflow-hidden h-100 hover-shadow">
                            <img src="https://picsum.photos/400/250?random={{ $i }}" class="card-img-top" alt="Room">
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
    <section id="promotions" class="py-5 text-center">
        <div class="container">
            <h2 class="fw-bold mb-4" data-aos="fade-down" data-aos-duration="1000">
                <x-icon name="discount-2-filled" class="text-warning me-2" width="36" height="36" />
                {{ __('landing.promotions.title', ['default' => 'Latest Promotions']) }}
            </h2>
            <p class="text-muted mb-5" data-aos="fade-up" data-aos-duration="1000">
                Save more with exclusive discounts and special offers available now!
            </p>
            <div class="row g-4 justify-content-center">
                @foreach ([['title' => '🔥 20% Off Monthly Rent', 'desc' => 'Book before end of October and enjoy 20% off your first month.', 'badge' => 'Limited Time'], ['title' => '🏠 Free Wi-Fi & Cleaning', 'desc' => 'All new tenants get free high-speed internet and weekly cleaning services.'], ['title' => '🎁 Referral Rewards', 'desc' => 'Invite a friend and both get $10 credit towards your next payment.']] as $index => $promo)
                    <div class="col-md-4" data-aos="fade-up" data-aos-duration="{{ 1000 + $index * 200 }}">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body text-start p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <x-icon name="star-filled" class="text-warning me-2" width="28" height="28" />
                                    <h5 class="mb-0 fw-semibold">{{ $promo['title'] }}</h5>
                                </div>
                                <p class="text-muted">{{ $promo['desc'] }}</p>
                                @if (isset($promo['badge']))
                                    <span
                                        class="badge bg-danger-subtle text-danger border border-danger px-3 py-1 rounded-pill">{{ $promo['badge'] }}</span>
                                @endif
                            </div>
                            <div class="card-footer bg-transparent border-0 text-center">
                                <a href="#" class="btn btn-outline-primary btn-sm mb-3">Learn More</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 👥 Careers --}}
    <section id="careers" class="py-5 text-center section">
        <div class="container">
            <h2 class="fw-bold mb-4" data-aos="fade-down" data-aos-duration="800">
                <x-icon name="briefcase-filled" class="text-primary me-2" width="32" height="32" />
                Join Our Team
            </h2>
            <p class="text-muted mb-5" data-aos="fade-up" data-aos-duration="1000">
                We’re hiring passionate people to help shape the future of housing in Cambodia.
            </p>
            <div class="row justify-content-center g-4">
                @foreach ([['title' => 'Frontend Developer', 'icon' => 'code'], ['title' => 'Customer Support', 'icon' => 'headphones'], ['title' => 'Marketing Specialist', 'icon' => 'megaphone']] as $index => $job)
                    <div class="col-md-4" data-aos="zoom-in-up" data-aos-duration="{{ 1000 + $index * 200 }}">
                        <div class="card h-100 shadow-sm rounded-4 p-4 text-start border-0 hover-shadow">
                            <x-icon name="{{ $job['icon'] }}-filled" class="text-primary mb-3" width="40" height="40" />
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
    <section id="contact" class="py-5">
        <div class="container">
            <h2 class="fw-bold text-center mb-4" data-aos="fade-down" data-aos-duration="800">
                {{ __('landing.contact.title') }}
            </h2>
            <form class="mx-auto" style="max-width: 600px;" data-aos="zoom-in" data-aos-duration="1200">
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
                <button class="btn btn-primary px-4 py-2">{{ __('landing.contact.send') }}</button>
            </form>
        </div>
    </section>

    {{-- ⚓ Footer --}}
    <footer class="py-4 bg-dark text-white text-center">
        <p class="mb-0" data-aos="fade-up" data-aos-duration="800">{{ __('landing.footer') }}</p>
    </footer>

@endsection
