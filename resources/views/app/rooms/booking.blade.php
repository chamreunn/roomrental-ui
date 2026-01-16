@extends('layouts.app')

@section('title', 'Room Details')

@section('content')

    <div class="row g-3">
        <!-- Left Info -->
        <div class="col-lg-4">
            <div class="row g-3">
                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="avatar avatar-xl bg-primary-lt mb-3">
                                <x-icon name="door" />
                            </div>

                            <h3 class="fw-bold mb-2">{{ $room['room_name'] }}</h3>
                            <p class="text-muted small mb-2">
                                {{ __('room.building') }} {{ $room['building_name'] }},
                                {{ __('room.floor') }} {{ $room['floor_name'] }}
                            </p>
                            <p class="text-muted">
                                <x-icon name="map-pin" />
                                {{ ucfirst($room['location']['location_name']) }}
                            </p>

                            <span class="{{ $roomstatus['badge'] }}">
                                {{ __($roomstatus['name']) }}
                            </span>
                        </div>

                        <div class="card-body text-center">
                            <div class="h1 text-success fw-bold mb-1">
                                ${{ number_format($room['room_type']['price'], 2) }}
                            </div>
                            <span class="text-muted mb-2"> / {{ __('room.per_month') }}</span>
                        </div>
                        <div class="card-body text-center">
                            <div class="text-muted mb-2">
                                <x-icon name="tag" />
                                {{ __('room.type') }}: <strong>{{ $room['room_type']['type_name'] }}</strong>
                            </div>
                            <div class="text-muted mb-2">
                                <x-icon name="ruler" />
                                {{ __('room.size') }}: <strong>{{ $room['room_type']['room_size'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Info -->
        <div class="col-lg-8">
            <!-- Description -->
            <div class="row g-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('tenant.information') }}</h3>
                        </div>
                        <form action="{{ route('client.store', ['id' => $room['id'], 'locationId' => $locationId]) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body d-flex align-items-center justify-content-center">
                                <div class="col-lg-4">
                                    {{-- Avatar container --}}
                                    <div class="position-relative d-inline-block" style="width: 200px; height: 200px;"
                                        title="{{ __('tenant.click_here_to_upload_you_profile') }}">
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
                                    <input type="file" name="image" id="avatarInput" class="d-none" accept="image/*">
                                    @error('image')
                                        <div class="text-red mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="card-body">
                                <fieldset class="form-fieldset">
                                    <div class="row g-3">
                                        <div class="col-lg-12">
                                            <div class="row g-3">
                                                {{-- ===================== Tenant Information ===================== --}}
                                                <div class="col-lg-12">
                                                    <div class="row g-3">

                                                        {{-- Full Name --}}
                                                        <div class="col-lg-4">
                                                            <label class="form-label required">
                                                                {{ __('tenant.full_name') }}
                                                            </label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <x-icon name="user" />
                                                                </span>
                                                                <input type="text" name="username" class="form-control"
                                                                    autocomplete="off"
                                                                    placeholder="{{ __('tenant.full_name_placeholder') }}"
                                                                    value="{{ old('username') }}">
                                                            </div>
                                                            @error('username')
                                                                <div class="text-danger mt-1">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        {{-- Date of Birth --}}
                                                        <div class="col-lg-4">
                                                            <label class="form-label required">
                                                                {{ __('tenant.date_of_birth') }}
                                                            </label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <x-icon name="calendar" />
                                                                </span>
                                                                <input type="text" name="dob" id="dob"
                                                                    class="form-control dobpicker"
                                                                    placeholder="{{ __('tenant.date_of_birth_placeholder') }}"
                                                                    value="{{ old('dob') }}" readonly>
                                                            </div>
                                                            @error('dob')
                                                                <div class="text-danger mt-1">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        {{-- Gender --}}
                                                        <div class="col-lg-4">
                                                            <label class="form-label required">
                                                                {{ __('tenant.gender') }}
                                                            </label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <x-icon name="users" />
                                                                </span>
                                                                <select name="gender" class="form-select tom-select">
                                                                    <option value="">{{ __('tenant.select_option') }}
                                                                    </option>
                                                                    <option value="f"
                                                                        {{ old('gender') === 'f' ? 'selected' : '' }}>
                                                                        {{ __('tenant.female') }}
                                                                    </option>
                                                                    <option value="m"
                                                                        {{ old('gender') === 'm' ? 'selected' : '' }}>
                                                                        {{ __('tenant.male') }}
                                                                    </option>
                                                                </select>
                                                            </div>
                                                            @error('gender')
                                                                <div class="text-danger mt-1">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                    </div>
                                                </div>

                                                {{-- ===================== Contact Info ===================== --}}
                                                <div class="col-12">
                                                    <div class="row g-3">
                                                        {{-- Phone Number --}}
                                                        <div class="col-lg-4">
                                                            <label
                                                                class="form-label required">{{ __('tenant.phone_number') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <x-icon name="phone" />
                                                                </span>
                                                                <input type="text" name="phone_number"
                                                                    class="form-control" autocomplete="off"
                                                                    placeholder="{{ __('tenant.phone_number_placeholder') }}"
                                                                    value="{{ old('phone_number') }}">
                                                            </div>
                                                            @error('phone_number')
                                                                <div class="text-danger mt-1">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        {{-- Email --}}
                                                        <div class="col-lg-4">
                                                            <label
                                                                class="form-label">{{ __('tenant.email_address') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <x-icon name="mail" />
                                                                </span>
                                                                <input type="email" name="email" class="form-control"
                                                                    autocomplete="off"
                                                                    placeholder="{{ __('tenant.email_placeholder') }}"
                                                                    value="{{ old('email') }}">
                                                            </div>
                                                            @error('email')
                                                                <div class="text-danger mt-1">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        {{-- National ID --}}
                                                        <div class="col-lg-4">
                                                            <label
                                                                class="form-label">{{ __('tenant.national_id') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <x-icon name="id" />
                                                                </span>
                                                                <input type="text" name="national_id"
                                                                    class="form-control" autocomplete="off"
                                                                    placeholder="{{ __('tenant.national_id_placeholder') }}"
                                                                    value="{{ old('national_id') }}">
                                                            </div>
                                                            @error('national_id')
                                                                <div class="text-danger mt-1">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- ===================== Identity & Dates ===================== --}}
                                                <div class="col-12">
                                                    <div class="row g-3">
                                                        {{-- Passport --}}
                                                        <div class="col-lg-6">
                                                            <label class="form-label">{{ __('tenant.passport') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <x-icon name="e-passport" />
                                                                </span>
                                                                <input type="text" name="passport"
                                                                    class="form-control" autocomplete="off"
                                                                    placeholder="{{ __('tenant.passport_placeholder') }}"
                                                                    value="{{ old('passport') }}">
                                                            </div>
                                                            @error('passport')
                                                                <div class="text-danger mt-1">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        {{-- Start Date --}}
                                                        <div class="col-lg-3">
                                                            <label
                                                                class="form-label required">{{ __('tenant.start_date') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <x-icon name="calendar-plus" />
                                                                </span>
                                                                <input type="text" name="start_rental_date"
                                                                    class="form-control datepicker"
                                                                    placeholder="{{ __('tenant.start_date_placeholder') }}"
                                                                    value="{{ old('start_rental_date') }}">
                                                            </div>
                                                            @error('start_rental_date')
                                                                <div class="text-danger mt-1">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        {{-- End Date --}}
                                                        <div class="col-lg-3">
                                                            <label class="form-label">{{ __('tenant.end_date') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <x-icon name="calendar-x" />
                                                                </span>
                                                                <input type="text" name="end_rental_date"
                                                                    class="form-control datepicker"
                                                                    placeholder="{{ __('tenant.end_date_placeholder') }}"
                                                                    value="{{ old('end_rental_date') }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- ===================== Address & Description ===================== --}}
                                                <div class="col-lg-12">
                                                    <label
                                                        class="form-label required">{{ __('tenant.current_address') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <x-icon name="map-pin" />
                                                        </span>
                                                        <textarea name="address" class="form-control" placeholder="{{ __('tenant.address_placeholder') }}">{{ old('address') }}</textarea>
                                                    </div>
                                                    @error('address')
                                                        <div class="text-danger mt-1">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-lg-12">
                                                    <label class="form-label">{{ __('tenant.description') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <x-icon name="file-text" />
                                                        </span>
                                                        <textarea name="description" class="form-control" placeholder="{{ __('tenant.description_placeholder') }}">{{ old('description') }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="card-footer">
                                <div class="row g-3">
                                    <div class="col">
                                        <button type="submit"
                                            class="btn btn-primary btn-animate-icon btn-animate-icon-rotate">
                                            {{ __('tenant.save') }}
                                            <x-icon name="plus" class="icon-end" />
                                        </button>
                                        <a href="{{ dashboardRoute() }}" class="btn">{{ __('tenant.cancel') }}</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
