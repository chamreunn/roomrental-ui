@extends('layouts.auth')

@section('page_title', 'ចូលប្រព័ន្ធ')

@section('content')

    <div
        class="col-12 col-lg-6 col-xl-4 border-top-wide border-primary d-flex flex-column justify-content-center">
        <div class="container container-tight my-5 px-lg-5">
            <div class="text-center mb-4">
                <!-- BEGIN NAVBAR LOGO -->
                <a href="{{ route('login') }}" aria-label="Tabler" class="navbar-brand navbar-brand-autodark">
                    <img src="{{ asset('favicon.ico') }}" class="border rounded shadow"  width="100" alt="">
                </a>
                <!-- END NAVBAR LOGO -->
            </div>

            <form action="/login" method="POST" novalidate="">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label required fw-bolder">អាសយដ្ឋានអ៊ីម៉ែល</label>
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <x-icon name="mail" class="text-primary mb-3" width="48" height="48" />
                            </span>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                                placeholder="អាសយដ្ឋានអ៉ីមែល">
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label required fw-bolder"> ពាក្យសម្ងាត់ </label>
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <x-icon name="lock-password" class="text-primary mb-3" width="48" height="48" />
                            </span>
                            <input type="password" id="password" class="form-control" name="password" value="{{ old('password') }}"
                                placeholder="ពាក្យសម្ងាត់">
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input cursor-pointer" id="togglePassword">
                            <span class="form-check-label cursor-pointer">បង្ហាញពាក្យសម្ងាត់</span>
                        </label>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-animate-icon btn-primary w-100">
                            ចូលប្រព័ន្ធ
                            <x-icon name="arrow-narrow-right" class="icon icon-end" />
                        </button>
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
