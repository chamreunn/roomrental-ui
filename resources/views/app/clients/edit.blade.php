@extends('layouts.app')

@section('content')
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <form action="{{ route('clients.update', [$client['id'], $client['room_id'], $locationId]) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="card-header">
                        <div class="row align-items-center w-100">
                            <div class="col">
                                <h3 class="card-title mb-1">
                                    <x-icon name="user-edit" />
                                    {{ __('Edit Tenant') }}
                                </h3>
                                <div class="text-secondary small">
                                    {{ __('Update tenant profile, booked room, rental dates, sub tenants and documents.') }}
                                </div>
                            </div>

                            <div class="col-auto">
                                <div class="btn-list">
                                    <a href="{{ route('clients.clients', $locationId) }}" class="btn btn-outline-secondary">
                                        <x-icon name="arrow-left" />
                                        {{ __('Back') }}
                                    </a>

                                    <button type="submit" class="btn btn-primary">
                                        <x-icon name="device-floppy" />
                                        {{ __('Save Changes') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @error('api')
                        <div class="alert alert-danger m-3 mb-0">
                            {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                        </div>
                    @enderror

                    @error('documents')
                        <div class="alert alert-warning m-3 mb-0">
                            {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                        </div>
                    @enderror

                    <div class="card-body">
                        <div class="row g-3">

                            {{-- Left panel --}}
                            <div class="col-lg-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <span class="avatar avatar-xl mb-3">
                                            <img id="avatarPreview"
                                                src="{{ $client['client_image_url'] ?: asset('imgs/default-avatar.png') }}"
                                                alt="{{ $client['username'] ?: __('Tenant') }}">
                                        </span>

                                        <h3 class="mb-1 text-truncate">
                                            {{ $client['username'] ?: __('Tenant') }}
                                        </h3>

                                        <div class="text-secondary small mb-3">
                                            {{ $client['room']['room_name'] ?? '-' }}
                                            ·
                                            {{ $client['room']['location_name'] ?? '-' }}
                                        </div>

                                        <label for="avatarInput" class="btn btn-outline-primary btn-sm">
                                            <x-icon name="camera" />
                                            {{ __('Change Photo') }}
                                        </label>

                                        <input type="file" name="image" id="avatarInput" class="d-none"
                                            accept="image/*">

                                        @error('image')
                                            <div class="text-danger small mt-2">
                                                {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Right summary --}}
                            <div class="col-lg-9">
                                <div class="row g-3">
                                    <div class="col-md-6 col-xl-3">
                                        <div class="card card-sm">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <span class="avatar bg-primary-lt text-primary">
                                                            <x-icon name="door" />
                                                        </span>
                                                    </div>
                                                    <div class="col">
                                                        <div class="text-secondary small">{{ __('room.room_name') }}</div>
                                                        <div class="fw-semibold text-truncate">
                                                            {{ $client['room']['room_name'] ?? '-' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-xl-3">
                                        <div class="card card-sm">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <span class="avatar bg-cyan-lt text-cyan">
                                                            <x-icon name="building" />
                                                        </span>
                                                    </div>
                                                    <div class="col">
                                                        <div class="text-secondary small">{{ __('Building / Floor') }}
                                                        </div>
                                                        <div class="fw-semibold text-truncate">
                                                            {{ $client['room']['building_name'] ?? '-' }}
                                                            ·
                                                            {{ $client['room']['floor_name'] ?? '-' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-xl-3">
                                        <div class="card card-sm">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <span class="avatar bg-purple-lt text-purple">
                                                            <x-icon name="category" />
                                                        </span>
                                                    </div>
                                                    <div class="col">
                                                        <div class="text-secondary small">{{ __('Room Type') }}</div>
                                                        <div class="fw-semibold text-truncate">
                                                            {{ $client['room']['type_name'] ?? '-' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-xl-3">
                                        <div class="card card-sm">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <span class="avatar bg-success-lt text-success">
                                                            <x-icon name="cash" />
                                                        </span>
                                                    </div>
                                                    <div class="col">
                                                        <div class="text-secondary small">{{ __('Price') }}</div>
                                                        <div class="fw-semibold text-truncate">
                                                            {{ $client['room']['price_text'] ?? '-' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="room_id" value="{{ old('room_id', $client['room_id']) }}">
                            </div>

                            {{-- Tenant information --}}
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <x-icon name="user" />
                                            {{ __('Tenant Information') }}
                                        </h3>
                                    </div>

                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-lg-4">
                                                <label class="form-label required">{{ __('client.username') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <x-icon name="user" />
                                                    </span>
                                                    <input type="text" name="username" class="form-control"
                                                        value="{{ old('username', $client['username']) }}"
                                                        autocomplete="off">
                                                </div>
                                                @error('username')
                                                    <div class="text-danger small mt-1">
                                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="col-lg-4">
                                                <label
                                                    class="form-label required">{{ __('client.date_of_birth') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <x-icon name="calendar" />
                                                    </span>
                                                    <input type="text" name="date_of_birth"
                                                        class="form-control datepicker"
                                                        value="{{ old('date_of_birth', $client['date_of_birth']) }}"
                                                        autocomplete="off">
                                                </div>
                                                @error('date_of_birth')
                                                    <div class="text-danger small mt-1">
                                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="col-lg-4">
                                                <label class="form-label required">{{ __('client.gender') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <x-icon name="users" />
                                                    </span>
                                                    <select name="gender" class="form-select tom-select">
                                                        <option value="">{{ __('client.select_option') }}</option>
                                                        <option value="m" @selected(old('gender', $client['gender_mapped']) === 'm')>
                                                            {{ __('client.male') }}
                                                        </option>
                                                        <option value="f" @selected(old('gender', $client['gender_mapped']) === 'f')>
                                                            {{ __('client.female') }}
                                                        </option>
                                                    </select>
                                                </div>
                                                @error('gender')
                                                    <div class="text-danger small mt-1">
                                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="col-lg-4">
                                                <label class="form-label required">{{ __('client.phone_number') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <x-icon name="phone" />
                                                    </span>
                                                    <input type="text" name="phone_number" class="form-control"
                                                        value="{{ old('phone_number', $client['phone_number']) }}"
                                                        autocomplete="off">
                                                </div>
                                                @error('phone_number')
                                                    <div class="text-danger small mt-1">
                                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="col-lg-4">
                                                <label class="form-label">{{ __('client.email') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <x-icon name="mail" />
                                                    </span>
                                                    <input type="email" name="email" class="form-control"
                                                        value="{{ old('email', $client['email']) }}" autocomplete="off">
                                                </div>
                                                @error('email')
                                                    <div class="text-danger small mt-1">
                                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="col-lg-4">
                                                <label class="form-label">{{ __('client.national_id') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <x-icon name="id" />
                                                    </span>
                                                    <input type="text" name="national_id" class="form-control"
                                                        value="{{ old('national_id', $client['national_id']) }}"
                                                        autocomplete="off">
                                                </div>
                                                @error('national_id')
                                                    <div class="text-danger small mt-1">
                                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="col-lg-4">
                                                <label class="form-label">{{ __('client.passport') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <x-icon name="e-passport" />
                                                    </span>
                                                    <input type="text" name="passport" class="form-control"
                                                        value="{{ old('passport', $client['passport']) }}"
                                                        autocomplete="off">
                                                </div>
                                                @error('passport')
                                                    <div class="text-danger small mt-1">
                                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="col-lg-4">
                                                <label
                                                    class="form-label required">{{ __('client.start_rental_date') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <x-icon name="calendar-plus" />
                                                    </span>
                                                    <input type="text" name="start_rental_date"
                                                        class="form-control datepicker"
                                                        value="{{ old('start_rental_date', $client['start_rental_date']) }}"
                                                        autocomplete="off">
                                                </div>
                                                @error('start_rental_date')
                                                    <div class="text-danger small mt-1">
                                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="col-lg-4">
                                                <label class="form-label">{{ __('client.end_rental_date') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <x-icon name="calendar-x" />
                                                    </span>
                                                    <input type="text" name="end_rental_date"
                                                        class="form-control datepicker"
                                                        value="{{ old('end_rental_date', $client['end_rental_date']) }}"
                                                        autocomplete="off">
                                                </div>
                                                @error('end_rental_date')
                                                    <div class="text-danger small mt-1">
                                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="col-lg-12">
                                                <label class="form-label required">{{ __('client.address') }}</label>
                                                <textarea name="address" class="form-control" rows="3" placeholder="{{ __('client.address') }}">{{ old('address', $client['address']) }}</textarea>
                                                @error('address')
                                                    <div class="text-danger small mt-1">
                                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="col-lg-12">
                                                <label class="form-label">{{ __('client.description') }}</label>
                                                <textarea name="description" class="form-control" rows="3" placeholder="{{ __('client.description') }}">{{ old('description', $client['description']) }}</textarea>
                                                @error('description')
                                                    <div class="text-danger small mt-1">
                                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Sub tenants --}}
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div>
                                            <h3 class="card-title">
                                                <x-icon name="users-plus" />
                                                {{ __('Sub Tenants') }}
                                            </h3>
                                            <div class="text-secondary small">
                                                {{ __('Manage family members, roommates, or other people staying with this tenant.') }}
                                            </div>
                                        </div>

                                        <div class="card-actions">
                                            <button type="button" class="btn btn-outline-primary btn-sm"
                                                id="addSubclientBtn">
                                                <x-icon name="plus" />
                                                {{ __('Add Sub Tenant') }}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <div id="deletedSubclientsWrapper"></div>

                                        <div id="subclientsWrapper">
                                            @foreach (old('subclients', $client['subclients'] ?? []) as $index => $subclient)
                                                <div class="card mb-3 subclient-item">
                                                    <div class="card-header">
                                                        <h3 class="card-title">
                                                            {{ __('Sub Tenant') }}
                                                        </h3>

                                                        <div class="card-actions">
                                                            <button type="button"
                                                                class="btn btn-outline-danger btn-sm remove-subclient"
                                                                data-existing-id="{{ $subclient['id'] ?? '' }}">
                                                                <x-icon name="trash" />
                                                                {{ __('Remove') }}
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="card-body">
                                                        <input type="hidden" data-name="id"
                                                            name="subclients[{{ $index }}][id]"
                                                            value="{{ $subclient['id'] ?? '' }}">

                                                        <div class="row g-3">
                                                            <div class="col-lg-12">
                                                                <div class="d-flex align-items-center gap-3">
                                                                    <span class="avatar avatar-lg">
                                                                        <img src="{{ $subclient['sub_client_image_url'] ?? asset('imgs/default-avatar.png') }}"
                                                                            alt="{{ $subclient['username'] ?? __('Sub Tenant') }}">
                                                                    </span>

                                                                    <div>
                                                                        <div class="fw-semibold">
                                                                            {{ $subclient['username'] ?? __('Sub Tenant') }}
                                                                        </div>
                                                                        <div class="text-secondary small">
                                                                            {{ __('Existing sub tenant information') }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-4">
                                                                <label
                                                                    class="form-label required">{{ __('tenant.full_name') }}</label>
                                                                <input type="text" data-name="username"
                                                                    name="subclients[{{ $index }}][username]"
                                                                    class="form-control"
                                                                    value="{{ old("subclients.$index.username", $subclient['username'] ?? '') }}">
                                                                @error("subclients.$index.username")
                                                                    <div class="text-danger small mt-1">
                                                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                                                    </div>
                                                                @enderror
                                                            </div>

                                                            <div class="col-lg-4">
                                                                <label
                                                                    class="form-label required">{{ __('tenant.date_of_birth') }}</label>
                                                                <input type="text" data-name="date_of_birth"
                                                                    name="subclients[{{ $index }}][date_of_birth]"
                                                                    class="form-control datepicker subclient-datepicker"
                                                                    value="{{ old("subclients.$index.date_of_birth", $subclient['date_of_birth'] ?? '') }}"
                                                                    autocomplete="off">
                                                                @error("subclients.$index.date_of_birth")
                                                                    <div class="text-danger small mt-1">
                                                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                                                    </div>
                                                                @enderror
                                                            </div>

                                                            <div class="col-lg-4">
                                                                <label
                                                                    class="form-label required">{{ __('tenant.gender') }}</label>
                                                                <select data-name="gender"
                                                                    name="subclients[{{ $index }}][gender]"
                                                                    class="form-select">
                                                                    <option value="">
                                                                        {{ __('tenant.select_option') }}</option>
                                                                    <option value="m" @selected(old("subclients.$index.gender", $subclient['gender_mapped'] ?? ($subclient['gender'] ?? '')) === 'm')>
                                                                        {{ __('tenant.male') }}
                                                                    </option>
                                                                    <option value="f" @selected(old("subclients.$index.gender", $subclient['gender_mapped'] ?? ($subclient['gender'] ?? '')) === 'f')>
                                                                        {{ __('tenant.female') }}
                                                                    </option>
                                                                </select>
                                                                @error("subclients.$index.gender")
                                                                    <div class="text-danger small mt-1">
                                                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                                                    </div>
                                                                @enderror
                                                            </div>

                                                            <div class="col-lg-4">
                                                                <label
                                                                    class="form-label">{{ __('tenant.phone_number') }}</label>
                                                                <input type="text" data-name="phone_number"
                                                                    name="subclients[{{ $index }}][phone_number]"
                                                                    class="form-control"
                                                                    value="{{ old("subclients.$index.phone_number", $subclient['phone_number'] ?? '') }}">
                                                            </div>

                                                            <div class="col-lg-4">
                                                                <label
                                                                    class="form-label">{{ __('tenant.email_address') }}</label>
                                                                <input type="email" data-name="email"
                                                                    name="subclients[{{ $index }}][email]"
                                                                    class="form-control"
                                                                    value="{{ old("subclients.$index.email", $subclient['email'] ?? '') }}">
                                                            </div>

                                                            <div class="col-lg-4">
                                                                <label
                                                                    class="form-label">{{ __('tenant.national_id') }}</label>
                                                                <input type="text" data-name="national_id"
                                                                    name="subclients[{{ $index }}][national_id]"
                                                                    class="form-control"
                                                                    value="{{ old("subclients.$index.national_id", $subclient['national_id'] ?? '') }}">
                                                            </div>

                                                            <div class="col-lg-4">
                                                                <label
                                                                    class="form-label">{{ __('tenant.passport') }}</label>
                                                                <input type="text" data-name="passport"
                                                                    name="subclients[{{ $index }}][passport]"
                                                                    class="form-control"
                                                                    value="{{ old("subclients.$index.passport", $subclient['passport'] ?? '') }}">
                                                            </div>

                                                            <div class="col-lg-4">
                                                                <label class="form-label">{{ __('Photo') }}</label>
                                                                <input type="file" data-name="sub_client_image"
                                                                    name="subclients[{{ $index }}][sub_client_image]"
                                                                    class="form-control" accept="image/*">
                                                            </div>

                                                            <div class="col-lg-12">
                                                                <label
                                                                    class="form-label required">{{ __('tenant.current_address') }}</label>
                                                                <textarea data-name="address" name="subclients[{{ $index }}][address]" class="form-control">{{ old("subclients.$index.address", $subclient['address'] ?? '') }}</textarea>
                                                                @error("subclients.$index.address")
                                                                    <div class="text-danger small mt-1">
                                                                        {{ is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message }}
                                                                    </div>
                                                                @enderror
                                                            </div>

                                                            <div class="col-lg-12">
                                                                <label
                                                                    class="form-label">{{ __('tenant.description') }}</label>
                                                                <textarea data-name="description" name="subclients[{{ $index }}][description]" class="form-control">{{ old("subclients.$index.description", $subclient['description'] ?? '') }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="empty" id="subclientEmptyState">
                                            <div class="empty-icon">
                                                <x-icon name="users" />
                                            </div>
                                            <p class="empty-title">
                                                {{ __('No sub tenants') }}
                                            </p>
                                            <p class="empty-subtitle text-secondary">
                                                {{ __('Add sub tenants if more people stay in this room.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Documents --}}
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div>
                                            <h3 class="card-title">
                                                <x-icon name="files" />
                                                {{ __('Documents') }}
                                            </h3>
                                            <div class="text-secondary small">
                                                {{ __('View existing documents, mark old documents for deletion, or upload new files.') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        @if (!empty($client['documents']))
                                            <div class="list-group mb-3">
                                                @foreach ($client['documents'] as $doc)
                                                    <div class="list-group-item">
                                                        <div class="row g-2 align-items-center">
                                                            <div class="col-auto">
                                                                <span class="avatar bg-primary-lt text-primary">
                                                                    <x-icon name="file" />
                                                                </span>
                                                            </div>

                                                            <div class="col text-truncate">
                                                                <div class="fw-semibold text-truncate">
                                                                    {{ $doc['file_name'] ?? __('Document') }}
                                                                </div>
                                                                <div class="text-secondary small">
                                                                    {{ __('Existing document') }}
                                                                </div>
                                                            </div>

                                                            <div class="col-auto">
                                                                <div class="btn-list">
                                                                    @if (!empty($doc['view_url']))
                                                                        <a href="{{ $doc['view_url'] }}"
                                                                            class="btn btn-sm btn-outline-primary"
                                                                            target="_blank">
                                                                            <x-icon name="eye" />
                                                                            {{ __('View') }}
                                                                        </a>
                                                                    @endif

                                                                    @if (!empty($doc['id']))
                                                                        <label class="form-check form-check-inline mb-0">
                                                                            <input class="form-check-input"
                                                                                type="checkbox" name="delete_documents[]"
                                                                                value="{{ $doc['id'] }}">
                                                                            <span class="form-check-label text-danger">
                                                                                {{ __('Delete') }}
                                                                            </span>
                                                                        </label>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="empty">
                                                <div class="empty-icon">
                                                    <x-icon name="file-off" />
                                                </div>
                                                <p class="empty-title">
                                                    {{ __('No documents') }}
                                                </p>
                                                <p class="empty-subtitle text-secondary">
                                                    {{ __('Upload documents for this tenant below.') }}
                                                </p>
                                            </div>
                                        @endif

                                        <label class="form-label">{{ __('Upload New Documents') }}</label>
                                        <input type="file" name="documents[]" class="form-control" multiple
                                            accept=".pdf,.png,.jpg,.jpeg,.doc,.docx">

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

                                        <div class="form-hint">
                                            {{ __('Accepted files: PDF, DOC, DOCX, JPG, JPEG, PNG. Max size: 10MB.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="btn-list justify-content-end">
                            <a href="{{ route('clients.clients', $locationId) }}" class="btn btn-outline-secondary">
                                <x-icon name="arrow-left" />
                                {{ __('Back') }}
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <x-icon name="device-floppy" />
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <template id="subclientTemplate">
        <div class="card mb-3 subclient-item">
            <div class="card-header">
                <h3 class="card-title">{{ __('Sub Tenant') }}</h3>

                <div class="card-actions">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-subclient">
                        <x-icon name="trash" />
                        {{ __('Remove') }}
                    </button>
                </div>
            </div>

            <div class="card-body">
                <input type="hidden" data-name="id" value="">

                <div class="row g-3">
                    <div class="col-lg-4">
                        <label class="form-label required">{{ __('tenant.full_name') }}</label>
                        <input type="text" data-name="username" class="form-control">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label required">{{ __('tenant.date_of_birth') }}</label>
                        <input type="text" data-name="date_of_birth"
                            class="form-control datepicker subclient-datepicker" autocomplete="off">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label required">{{ __('tenant.gender') }}</label>
                        <select data-name="gender" class="form-select">
                            <option value="">{{ __('tenant.select_option') }}</option>
                            <option value="m">{{ __('tenant.male') }}</option>
                            <option value="f">{{ __('tenant.female') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">{{ __('tenant.phone_number') }}</label>
                        <input type="text" data-name="phone_number" class="form-control">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">{{ __('tenant.email_address') }}</label>
                        <input type="email" data-name="email" class="form-control">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">{{ __('tenant.national_id') }}</label>
                        <input type="text" data-name="national_id" class="form-control">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">{{ __('tenant.passport') }}</label>
                        <input type="text" data-name="passport" class="form-control">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">{{ __('Photo') }}</label>
                        <input type="file" data-name="sub_client_image" class="form-control" accept="image/*">
                    </div>

                    <div class="col-lg-12">
                        <label class="form-label required">{{ __('tenant.current_address') }}</label>
                        <textarea data-name="address" class="form-control"></textarea>
                    </div>

                    <div class="col-lg-12">
                        <label class="form-label">{{ __('tenant.description') }}</label>
                        <textarea data-name="description" class="form-control"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const avatarInput = document.getElementById('avatarInput');
            const avatarPreview = document.getElementById('avatarPreview');

            if (avatarInput && avatarPreview) {
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
            }

            const wrapper = document.getElementById('subclientsWrapper');
            const template = document.getElementById('subclientTemplate');
            const addButton = document.getElementById('addSubclientBtn');
            const emptyState = document.getElementById('subclientEmptyState');
            const deletedWrapper = document.getElementById('deletedSubclientsWrapper');

            function syncEmptyState() {
                if (!emptyState || !wrapper) {
                    return;
                }

                emptyState.classList.toggle(
                    'd-none',
                    wrapper.querySelectorAll('.subclient-item').length > 0
                );
            }

            function reindexSubclients() {
                if (!wrapper) {
                    return;
                }

                wrapper.querySelectorAll('.subclient-item').forEach(function(item, index) {
                    item.querySelectorAll('[data-name]').forEach(function(input) {
                        input.name = `subclients[${index}][${input.dataset.name}]`;
                    });

                    item.querySelectorAll('[data-name="date_of_birth"]').forEach(function(input) {
                        input.classList.add('datepicker', 'subclient-datepicker');
                        input.setAttribute('autocomplete', 'off');
                    });
                });

                syncEmptyState();

                if (window.initDatepickers) {
                    window.initDatepickers(wrapper);
                }
            }

            if (addButton && template && wrapper) {
                addButton.addEventListener('click', function() {
                    const clone = template.content.cloneNode(true);
                    wrapper.appendChild(clone);
                    reindexSubclients();
                });
            }

            document.addEventListener('click', function(event) {
                const removeButton = event.target.closest('.remove-subclient');

                if (!removeButton) {
                    return;
                }

                const existingId = removeButton.dataset.existingId;

                if (existingId && deletedWrapper) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_subclients[]';
                    input.value = existingId;
                    deletedWrapper.appendChild(input);
                }

                removeButton.closest('.subclient-item')?.remove();
                reindexSubclients();
            });

            reindexSubclients();
        });
    </script>
@endpush
