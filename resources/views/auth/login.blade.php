@extends('layouts.auth')

@section('content')

    <div class="col-12 col-lg-6 col-xl-4 border-top-wide border-primary d-flex flex-column justify-content-center">
        <div class="container container-tight my-5 px-lg-5">
            <div class="text-center mb-4">
                <!-- BEGIN NAVBAR LOGO -->
                <a href="{{ route('login') }}" aria-label="Tabler" class="navbar-brand navbar-brand-autodark">
                    <img src="{{ asset('favicon.ico') }}" width="100" alt="">
                </a>
                <!-- END NAVBAR LOGO -->
            </div>

            <form action="{{ route('login.submit') }}" method="POST" novalidate>
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label required fw-bolder">{{ __('auth.email_address') }}</label>
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <x-icon name="mail" />
                            </span>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                                placeholder="{{ __('auth.email_required') }}" autofocus autocomplete="off">
                        </div>
                        @error('email')
                            <div class="text-red mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label required fw-bolder">{{ __('auth.password') }}</label>
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <x-icon name="lock-password" />
                            </span>
                            <input type="password" id="password" class="form-control" name="password"
                                placeholder="{{ __('auth.password_required') }}">
                        </div>
                        @error('password')
                            <div class="text-red mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input cursor-pointer" id="togglePassword">
                            <span class="form-check-label cursor-pointer">បង្ហាញពាក្យសម្ងាត់</span>
                        </label>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-animate-icon btn-primary w-100">
                            <span class="me-2">ចូលប្រព័ន្ធ</span>
                            <x-icon name="arrow-narrow-right" class="icon icon-2 icon-end" />
                        </button>
                    </div>

                    <div class="hr-text">ឬ</div>
                    <div class="col-12">
                        <a href="#" class="btn w-100">ចូលប្រើប្រាស់ជាមួយគណនី Telegram</a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="col-12 col-lg-6 col-xl-8 d-none d-lg-block">
        <!-- Photo -->
        <div class="bg-cover h-100 min-vh-100"
            style="background-image: url(https://lesroches.edu/wp-content/uploads/2023/07/Hotel-property-management-systems.jpg)">
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('change', function() {
            const passwordField = document.getElementById('password');
            passwordField.type = this.checked ? 'text' : 'password';
        });
    </script>
@endpush
