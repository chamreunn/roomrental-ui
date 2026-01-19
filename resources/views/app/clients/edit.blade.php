@extends('layouts.app')

@section('content')
    <!-- Description -->
    <div class="row g-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('tenant.information') }}</h3>
                </div>
                <form action="{{ route('clients.update', [$client['id'], $client['room']['id'], $locationId]) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="card-body d-flex justify-content-center">
                        <div class="text-center">
                            {{-- Avatar --}}
                            <div class="position-relative" style="width:200px; height:200px;">
                                <img id="avatarPreview"
                                    src="{{ apiBaseUrl() . $client['client_image'] ?? '/imgs/default-avatar.png' }}"
                                    alt="Profile Picture" class="rounded border"
                                    style="width:100%; height:100%; object-fit:cover; cursor:pointer;">
                                <div id="uploadOverlay"
                                    class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center rounded"
                                    style="background: rgba(0,0,0,0.4); color:#fff; opacity:0; transition:opacity 0.3s; cursor:pointer;">
                                    <i class="bi bi-camera-fill fs-3"></i>
                                </div>
                            </div>
                            <input type="file" name="image" id="avatarInput" class="d-none" accept="image/*">
                            @error('image')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Username --}}
                            <div class="col-lg-4">
                                <label class="form-label required">{{ __('client.username') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="user" /></span>
                                    <input type="text" name="username" class="form-control"
                                        value="{{ old('username', $client['username']) }}">
                                </div>
                                @error('username')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Date of Birth --}}
                            <div class="col-lg-4">
                                <label class="form-label required">{{ __('client.date_of_birth') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="calendar" /></span>
                                    <input type="text" name="date_of_birth" class="form-control datepicker"
                                        value="{{ old('date_of_birth', $client['date_of_birth']) }}">
                                </div>
                                @error('date_of_birth')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Gender --}}
                            <div class="col-lg-4">
                                <label class="form-label required">{{ __('client.gender') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="users" /></span>
                                    <select name="gender" class="form-select tom-select">
                                        <option value="">{{ __('client.select_option') }}</option>
                                        <option value="m"
                                            {{ old('gender', $client['gender_mapped']) === 'm' ? 'selected' : '' }}>
                                            {{ __('client.male') }}
                                        </option>
                                        <option value="f"
                                            {{ old('gender', $client['gender_mapped']) === 'f' ? 'selected' : '' }}>
                                            {{ __('client.female') }}
                                        </option>
                                    </select>
                                </div>
                                @error('gender')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Phone --}}
                            <div class="col-lg-4">
                                <label class="form-label required">{{ __('client.phone_number') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="phone" /></span>
                                    <input type="text" name="phone_number" class="form-control"
                                        value="{{ old('phone_number', $client['phone_number']) }}">
                                </div>
                                @error('phone_number')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-lg-4">
                                <label class="form-label">{{ __('client.email') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="mail" /></span>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ old('email', $client['email']) }}">
                                </div>
                                @error('email')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Room --}}
                            <div class="col-lg-4">
                                <label class="form-label">{{ __('client.room') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="door" /></span>
                                    <input type="text" class="form-control" readonly
                                        value="{{ $client['room']['building_name'] ?? '' }} {{ $client['room']['room_name'] ?? '' }}">
                                </div>
                            </div>

                            {{-- Start / End Rental --}}
                            <div class="col-lg-6">
                                <label class="form-label">{{ __('client.start_rental_date') }}</label>
                                <input type="text" name="start_rental_date" class="form-control datepicker"
                                    value="{{ old('start_rental_date', $client['start_rental_date']) }}">
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label">{{ __('client.end_rental_date') }}</label>
                                <input type="text" name="end_rental_date" class="form-control datepicker"
                                    value="{{ old('end_rental_date', $client['end_rental_date']) }}">
                            </div>

                            {{-- Address --}}
                            <div class="col-lg-12">
                                <label class="form-label required">{{ __('client.address') }}</label>
                                <textarea name="address" class="form-control">{{ old('address', $client['address']) }}</textarea>
                                @error('address')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div class="col-lg-12">
                                <label class="form-label">{{ __('client.description') }}</label>
                                <textarea name="description" class="form-control">{{ old('description', $client['description']) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="save" class="me-1" /> {{ __('client.save') }}
                        </button>
                        <a href="{{ route('clients.clients', $locationId) }}"
                            class="btn btn-secondary">{{ __('client.cancel') }}</a>
                    </div>
                </form>
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
