@extends('layouts.app')

@section('content')
    <form action="{{ route('account.store') }}" method="POST" enctype="multipart/form-data">
        <div class="row row-cards">
            <div class="col-lg-8">
                <div class="card">
                    @csrf
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Username --}}
                            <div class="col-lg-4">
                                <label for="username" class="form-label required">{{ __('account.username') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="user" /></span>
                                    <input type="text" id="username" class="form-control" name="name"
                                        placeholder="{{ __('account.username') }}" value="{{ old('name') }}" autofocus>
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
                                            <option value="{{ $roleKey }}"
                                                {{ old('role', $selectedRole ?? '') == $roleKey ? 'selected' : '' }}
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
                                        placeholder="{{ __('account.email') }}" value="{{ old('email') }}">
                                </div>
                                @error('email')
                                    <div class="text-red mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Phone Number --}}
                            <div class="col-lg-4">
                                <label for="phone" class="form-label required">{{ __('account.phone') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="phone" /></span>
                                    <input type="tel" id="phone" class="form-control" name="phone_number"
                                        placeholder="{{ __('account.phone_placeholder') }}"
                                        value="{{ old('phone_number') }}">
                                </div>
                                @error('phone_number')
                                    <div class="text-red mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Date of Birth --}}
                            <div class="col-lg-4">
                                <label for="dob" class="form-label required">{{ __('account.date_of_birth') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="calendar-week" /></span>
                                    <input type="text" id="dob" class="form-control datepicker" name="dob"
                                        placeholder="{{ __('account.date_of_birth') }}" value="{{ old('dob') }}"
                                        readonly>
                                </div>
                                @error('dob')
                                    <div class="text-red mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Password --}}
                            <div class="col-lg-4">
                                <label for="password" class="form-label required">{{ __('account.password') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="lock" /></span>
                                    <input type="password" id="password" class="form-control" name="password"
                                        placeholder="{{ __('account.password') }}">
                                </div>
                                @error('password')
                                    <div class="text-red mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Address --}}
                            <div class="col-lg-8">
                                <label for="address" class="form-label">{{ __('account.address') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="home" /></span>
                                    <textarea id="address" class="form-control" name="address" rows="2"
                                        placeholder="{{ __('account.address_placeholder') }}">{{ old('address') }}</textarea>
                                </div>
                                @error('address')
                                    <div class="text-red mt-1">{{ $message }}</div>
                                @enderror
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
                                        value="{{ $location['id'] }}" type="checkbox"
                                        {{ in_array($location['id'], old('location_id', [])) ? 'checked' : '' }}>
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
                            {{ __('account.save') }}
                            <x-icon name="plus" class="icon-end" />
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="mb-3">{{ __('account.choose_profile_user') }}</h5>

                        {{-- Avatar container --}}
                        <div class="position-relative d-inline-block" style="width: 300px; height: 300px;">
                            <img id="avatarPreview" src="{{ old('avatar', '/imgs/default-avatar.png') }}"
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
                            <div class="text-red mt-1">{{ $message }}</div>
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
        avatarInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => avatarPreview.src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    </script>
@endpush
