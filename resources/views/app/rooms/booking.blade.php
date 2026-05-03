@extends('layouts.app')

@section('title', __('Room Booking'))

@section('content')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <span class="avatar avatar-xl bg-primary-lt text-primary mb-3">
                        <x-icon name="door" />
                    </span>

                    <h3 class="fw-bold mb-2">{{ $room['room_name'] ?? '-' }}</h3>

                    <div class="text-secondary small mb-2">
                        {{ __('room.building') }} {{ $room['building_name'] ?? '-' }}
                        ·
                        {{ __('room.floor') }} {{ $room['floor_name'] ?? '-' }}
                    </div>

                    <div class="text-secondary mb-3">
                        <x-icon name="map-pin" />
                        {{ $room['location']['location_name'] ?? '-' }}
                    </div>

                    <span class="{{ $roomstatus['badge'] ?? 'badge bg-secondary-lt text-secondary' }}">
                        {{ __($roomstatus['name'] ?? 'Unknown') }}
                    </span>
                </div>

                <div class="card-body text-center border-top">
                    <div class="h1 text-success fw-bold mb-1">
                        {{ number_format((float) data_get($room, 'room_type.price', 0), 2) }}៛
                    </div>
                    <span class="text-secondary">/ {{ __('room.per_month') }}</span>
                </div>

                <div class="card-body border-top">
                    <div class="divide-y">
                        <div class="row py-2">
                            <div class="col text-secondary">{{ __('room.type') }}</div>
                            <div class="col-auto fw-medium">
                                {{ data_get($room, 'room_type.type_name', '-') }}
                            </div>
                        </div>

                        <div class="row py-2">
                            <div class="col text-secondary">{{ __('room.size') }}</div>
                            <div class="col-auto fw-medium">
                                {{ data_get($room, 'room_type.room_size', '-') }}
                            </div>
                        </div>

                        <div class="row py-2">
                            <div class="col text-secondary">{{ __('location.name') }}</div>
                            <div class="col-auto fw-medium">
                                {{ data_get($room, 'location.location_name', '-') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <a href="{{ route('room.show', ['room_id' => $room['id'], 'location_id' => $locationId]) }}"
                        class="btn btn-outline-secondary w-100">
                        <x-icon name="arrow-left" />
                        {{ __('Back to Room') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <form action="{{ route('client.store', ['id' => $room['id'], 'locationId' => $locationId]) }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                @error('api')
                    <div class="alert alert-danger">
                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                    </div>
                @enderror

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <x-icon name="user-plus" />
                            {{ __('Main Tenant Information') }}
                        </h3>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-12 text-center">
                                <span class="avatar avatar-xl mb-3">
                                    <img id="avatarPreview" src="{{ asset('imgs/default-avatar.png') }}"
                                        alt="Profile Picture">
                                </span>

                                <div>
                                    <label for="avatarInput" class="btn btn-outline-primary btn-sm">
                                        <x-icon name="camera" />
                                        {{ __('Upload Photo') }}
                                    </label>
                                    <input type="file" name="image" id="avatarInput" class="d-none" accept="image/*">
                                </div>

                                @error('image')
                                    <div class="text-danger small mt-2">
                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label required">{{ __('tenant.full_name') }}</label>
                                <input type="text" name="username" class="form-control" value="{{ old('username') }}"
                                    placeholder="{{ __('tenant.full_name_placeholder') }}">
                                @error('username')
                                    <div class="text-danger small mt-1">
                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label required">{{ __('tenant.date_of_birth') }}</label>
                                <input type="text" name="dob" class="form-control dobpicker"
                                    value="{{ old('dob') }}"
                                    placeholder="{{ __('tenant.date_of_birth_placeholder') }}">
                                @error('dob')
                                    <div class="text-danger small mt-1">
                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label required">{{ __('tenant.gender') }}</label>
                                <select name="gender" class="form-select tom-select">
                                    <option value="">{{ __('tenant.select_option') }}</option>
                                    <option value="m" @selected(old('gender') === 'm')>{{ __('tenant.male') }}</option>
                                    <option value="f" @selected(old('gender') === 'f')>{{ __('tenant.female') }}</option>
                                </select>
                                @error('gender')
                                    <div class="text-danger small mt-1">
                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label required">{{ __('tenant.phone_number') }}</label>
                                <input type="text" name="phone_number" class="form-control"
                                    value="{{ old('phone_number') }}"
                                    placeholder="{{ __('tenant.phone_number_placeholder') }}">
                                @error('phone_number')
                                    <div class="text-danger small mt-1">
                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label">{{ __('tenant.email_address') }}</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                                    placeholder="{{ __('tenant.email_placeholder') }}">
                                @error('email')
                                    <div class="text-danger small mt-1">
                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label">{{ __('tenant.national_id') }}</label>
                                <input type="text" name="national_id" class="form-control"
                                    value="{{ old('national_id') }}"
                                    placeholder="{{ __('tenant.national_id_placeholder') }}">
                                @error('national_id')
                                    <div class="text-danger small mt-1">
                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label">{{ __('tenant.passport') }}</label>
                                <input type="text" name="passport" class="form-control"
                                    value="{{ old('passport') }}" placeholder="{{ __('tenant.passport_placeholder') }}">
                                @error('passport')
                                    <div class="text-danger small mt-1">
                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label required">{{ __('tenant.start_date') }}</label>
                                <input type="text" name="start_rental_date" class="form-control datepicker"
                                    value="{{ old('start_rental_date') }}"
                                    placeholder="{{ __('tenant.start_date_placeholder') }}">
                                @error('start_rental_date')
                                    <div class="text-danger small mt-1">
                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label">{{ __('tenant.end_date') }}</label>
                                <input type="text" name="end_rental_date" class="form-control datepicker"
                                    value="{{ old('end_rental_date') }}"
                                    placeholder="{{ __('tenant.end_date_placeholder') }}">
                                @error('end_rental_date')
                                    <div class="text-danger small mt-1">
                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-lg-12">
                                <label class="form-label required">{{ __('tenant.current_address') }}</label>
                                <textarea name="address" class="form-control" placeholder="{{ __('tenant.address_placeholder') }}">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="text-danger small mt-1">
                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-lg-12">
                                <label class="form-label">{{ __('tenant.description') }}</label>
                                <textarea name="description" class="form-control" placeholder="{{ __('tenant.description_placeholder') }}">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="text-danger small mt-1">
                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-lg-12">
                                <label class="form-label required">{{ __('tenant.document') }}</label>
                                <input type="file" name="documents[]" class="form-control" multiple
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <div class="form-hint">
                                    {{ __('Accepted files: PDF, DOC, DOCX, JPG, JPEG, PNG. Max size: 10MB.') }}
                                </div>
                                @error('documents')
                                    <div class="text-danger small mt-1">
                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                    </div>
                                @enderror
                                @error('documents.*')
                                    <div class="text-danger small mt-1">
                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                @include('app.clients.partials.subclients-form', [
                    'subclients' => old('subclients', []),
                    'mode' => 'create',
                ])

                <div class="card">
                    <div class="card-footer">
                        <div class="btn-list justify-content-end">
                            <a href="{{ route('room.show', ['room_id' => $room['id'], 'location_id' => $locationId]) }}"
                                class="btn btn-outline-secondary">
                                <x-icon name="arrow-left" />
                                {{ __('Cancel') }}
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <x-icon name="calendar-plus" />
                                {{ __('Book Room') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @include('app.clients.partials.subclients-script')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const avatarInput = document.getElementById('avatarInput');
            const avatarPreview = document.getElementById('avatarPreview');

            if (!avatarInput || !avatarPreview) {
                return;
            }

            avatarInput.addEventListener('change', function(event) {
                const file = event.target.files[0];

                if (!file) {
                    return;
                }

                const reader = new FileReader();

                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                };

                reader.readAsDataURL(file);
            });
        });
    </script>
@endpush
