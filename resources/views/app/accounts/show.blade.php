@extends('layouts.app')

@section('content')

    <form action="{{ route('account.update', $user['id']) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div class="row row-cards">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Username --}}
                            <div class="col-lg-4">
                                <label for="username" class="form-label required">{{ __('account.username') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="user" /></span>
                                    <input type="text" id="username" class="form-control" name="name"
                                        placeholder="{{ __('account.username') }}"
                                        value="{{ old('name', $user['name'] ?? '') }}" autofocus>
                                </div>
                                @error('name')
                                    <div class="text-red mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Role --}}
                            <div class="col-lg-4">
                                <label for="roleId" class="form-label required">{{ __('account.role') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="shield" /></span>
                                    <select name="role" id="roleId" class="form-select tom-select"
                                        data-placeholder="{{ __('account.select_a_role') }}">
                                        <option value="">{{ __('account.select_a_role') }}</option>
                                        @foreach ($roles as $roleKey => $role)
                                            <option value="{{ $roleKey }}" {{ old('role', $user['role'] ?? ($selectedRole ?? '')) == $roleKey ? 'selected' : '' }}
                                                data-custom-properties="<span class='{{ $role['class'] }} badge mx-0'>{{ ucfirst($role['name']) }}</span>">
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('role')
                                    <div class="text-red mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-lg-4">
                                <label for="email" class="form-label required">{{ __('account.email') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="mail" /></span>
                                    <input type="email" id="email" class="form-control" name="email"
                                        value="{{ old('email', $user['email'] ?? '') }}">
                                </div>
                            </div>

                            {{-- Phone Number --}}
                            <div class="col-lg-4">
                                <label for="phone" class="form-label required">{{ __('account.phone') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="phone" /></span>
                                    <input type="tel" id="phone" class="form-control" name="phone_number"
                                        value="{{ old('phone_number', $user['phone_number'] ?? '') }}">
                                </div>
                            </div>

                            {{-- DOB --}}
                            <div class="col-lg-4">
                                <label for="dob" class="form-label required">{{ __('account.date_of_birth') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="calendar-week" /></span>
                                    <input type="text" id="dob" class="form-control datepicker" name="dob"
                                        value="{{ old('dob', \Carbon\Carbon::parse($user['date_of_birth'] ?? '')->format('d-m-Y')) }}"
                                        readonly>
                                </div>
                            </div>

                            {{-- Password (optional) --}}
                            <div class="col-lg-4">
                                <label for="password" class="form-label">{{ __('account.password') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="lock" /></span>
                                    <input type="password" id="password" class="form-control" name="password"
                                        placeholder="{{ __('account.password') }}">
                                </div>
                            </div>

                            {{-- Address --}}
                            <div class="col-lg-8">
                                <label for="address" class="form-label">{{ __('account.address') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="home" /></span>
                                    <textarea id="address" class="form-control" name="address"
                                        rows="2">{{ old('address', $user['address'] ?? '') }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Locations --}}
                    <div class="card-body">
                        <div class="form-label">ទីតាំងអ្នកប្រើប្រាស់</div>
                        <div>
                            @foreach ($locations as $location)
                                <label class="form-check form-check-inline cursor-pointer">
                                    <input class="form-check-input cursor-pointer" name="location_id[]"
                                        value="{{ $location['id'] }}" type="checkbox" {{ in_array($location['id'], old('location_id', $user['user_locations'] ?? [])) ? 'checked' : '' }}>
                                    <span class="form-check-label cursor-pointer">{{ $location['location_name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('location_id')
                            <div class="text-red mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="card-footer">
                        <button class="btn btn-primary ms-auto btn-animate-icon btn-animate-icon-rotate">
                            {{ __('account.save_update') }}
                            <x-icon name="refresh" class="icon-end" />
                        </button>
                    </div>
                </div>
            </div>

            {{-- Avatar Section --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="mb-3">{{ __('account.update_profile_user') }}</h5>

                        {{-- Avatar container --}}
                        <div class="position-relative d-inline-block" style="width: 300px; height: 300px;">
                            <img id="avatarPreview"
                                src="{{ apiBaseUrl() . $user['profile_picture'] ?? '/imgs/default-avatar.png' }}"
                                alt="Profile Picture" class="rounded border"
                                style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;">

                            {{-- Overlay icon --}}
                            <div id="uploadOverlay"
                                class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center rounded"
                                style="background: rgba(0,0,0,0.4); color: #fff; opacity: 0; transition: opacity 0.3s; cursor: pointer;">
                                <i class="bi bi-camera-fill fs-3"></i>
                            </div>
                        </div>

                        {{-- Hidden file input --}}
                        <input type="file" name="profile_picture" id="avatarInput" class="d-none" accept="image/*">
                        @error('profile_picture')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection

@push('scripts')
    <script>
        const avatarPreview = document.getElementById('avatarPreview');
        const avatarInput = document.getElementById('avatarInput');
        const overlay = document.getElementById('uploadOverlay');

        // Show overlay on hover
        avatarPreview.addEventListener('mouseenter', () => overlay.style.opacity = 1);
        avatarPreview.addEventListener('mouseleave', () => overlay.style.opacity = 0);

        // Click overlay or image to open file dialog
        overlay.addEventListener('click', () => avatarInput.click());
        avatarPreview.addEventListener('click', () => avatarInput.click());

        // Preview selected image
        avatarInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => avatarPreview.src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    </script>
@endpush
