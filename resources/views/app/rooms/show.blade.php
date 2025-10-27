@extends('layouts.app')

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

                            <form action="{{ route('room.update-status', [$room['id'], $room['location']['id']]) }}"
                                method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')

                                <div class="dropdown d-inline-block mb-2">
                                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        <x-icon name="pencil" class="me-1" /> {{ __('room.edit_status') }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @foreach ($statuses as $key => $status)
                                            <li>
                                                <button type="submit" name="status" value="{{ $key }}"
                                                    class="dropdown-item">
                                                    <span class="{{ $status['text'] }}">
                                                        {{ $status['name'] }}
                                                    </span>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </form>

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
                @if (userRole() != 'user')
                    <div class="col-12">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <a href="{{ route('room.edit', ['room_id' => $room['id'], 'location_id' => $room['location_id']]) }}"
                                    class="btn btn-primary w-100">
                                    <x-icon name="edit" />{{ __('titles.edit') }}
                                </a>
                            </div>
                            <div class="col-lg-6">
                                <button class="btn btn-danger w-100" data-bs-toggle="modal"
                                    data-bs-target="#{{ $room['id'] }}">
                                    <x-icon name="trash" />
                                    {{ __('titles.delete') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Info -->
        <div class="col-lg-8">
            <!-- Description -->
            <div class="row g-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">
                                <x-icon name="info-circle" class="me-2 text-primary" />
                                {{ __('room.information') }}
                            </h5>
                            <p class="mb-0">
                                <x-empty-state title="{{ __('room.no_information_found') }}"
                                    message="{{ __('room.this_room_is_no_information') }}" svg="svgs/no_result.svg"
                                    width="200px" />
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Clients -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">
                                <x-icon name="users" class="me-2 text-primary" />
                                {{ __('room.clients') }}
                            </h5>
                            @if (empty($room['clients']))
                                {{-- Display Empty State if no clients are found --}}
                                <x-empty-state title="{{ __('room.no_client_found') }}"
                                    message="{{ __('room.there_are_no_client_in_this_room') }}" svg="svgs/no_result.svg"
                                    width="200px" />
                            @else
                                {{-- Client Cards/List Container (using Tabler Cards for better visual separation) --}}
                                <div class="row row-cards g-2">
                                    @foreach ($room['clients'] as $client)
                                        <div class="col-12">
                                            <div class="card card-sm">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">

                                                        {{-- Client Avatar/Initial --}}
                                                        <span
                                                            class="avatar me-3 bg-{{ $client['gender'] == 'ប្រុស' ? 'blue' : 'pink' }} text-primary-fg">
                                                            {{ strtoupper(substr($client['username'], 0, 1)) }}
                                                        </span>

                                                        <div class="flex-fill">
                                                            <div class="font-weight-medium">{{ $client['username'] }}</div>
                                                            {{-- Phone/Email/Address line --}}
                                                            <div class="text-muted">
                                                                <span class="d-none d-lg-inline-block">
                                                                    <x-icon name="phone" class="me-1" width="16" />
                                                                    {{ $client['phone_number'] }}
                                                                </span>
                                                                <span class="mx-2 d-none d-lg-inline-block text-dot"></span>
                                                                <span class="d-none d-lg-inline-block">
                                                                    <x-icon name="map-pin" class="me-1" width="16" />
                                                                    {{ $client['address'] }}
                                                                </span>
                                                            </div>
                                                        </div>

                                                        {{-- Status and Dates --}}
                                                        <div class="d-flex flex-column align-items-end me-3">

                                                            {{-- Rental Status Badge --}}
                                                            @if ($client['end_rental_date'])
                                                                <span class="badge bg-danger-lt">Lease Ended</span>
                                                            @elseif ($client['status'] == 1)
                                                                <span class="badge bg-success-lt">Renting Now</span>
                                                            @else
                                                                <span class="badge bg-warning-lt">Pending</span>
                                                            @endif

                                                            {{-- Start Date (Visually smaller) --}}
                                                            <div class="text-muted fs-6 mt-1">
                                                                Since:
                                                                {{ \Carbon\Carbon::parse($client['start_rental_date'])->format('d M Y') }}
                                                            </div>
                                                        </div>

                                                        {{-- Action Dropdown (More Options) --}}
                                                        <div class="dropdown">
                                                            <a href="#" class="btn-action" data-bs-toggle="dropdown"
                                                                aria-expanded="false">
                                                                <x-icon name="dots-vertical" />
                                                            </a>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a href="#" class="dropdown-item">View Profile</a>
                                                                <a href="#" class="dropdown-item text-danger">End
                                                                    Lease</a>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- modal delete --}}
    <div class="modal modal-blur fade" id="{{ $room['id'] }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <form action="{{ route('room.destroy', ['room_id' => $room['id'], 'location_id' => $room['location_id']]) }}"
                method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="modal-title">{{ __('modal.confirm_title') }}</div>
                        <div>{{ __('modal.confirm_message_room') }} <span
                                class="badge bg-primary-lt">{{ $room['room_name'] }}</span> ?</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">
                            {{ __('modal.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-danger">
                            {{ __('modal.confirm') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection
