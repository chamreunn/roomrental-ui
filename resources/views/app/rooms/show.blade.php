@extends('layouts.app')

@section('title', 'Room Details')

@section('content')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ $room['room_name'] }}</h2>
            <p class="text-muted mb-0">
                Building {{ $room['building_name'] }}, Floor {{ $room['floor_name'] }} •
                <i class="ti ti-map-pin"></i> {{ ucfirst($room['location']['location_name']) }}
            </p>
        </div>
        <a href="{{ route('room.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row g-4">
        <!-- Left Panel -->
        <div class="col-lg-4">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <div class="avatar avatar-xl bg-primary-lt mb-3">
                        <i class="ti ti-door text-primary fs-2"></i>
                    </div>
                    <h4 class="fw-bold">{{ $room['room_type']['type_name'] }}</h4>
                    <div class="text-muted mb-3">Room Type</div>

                    <div class="h2 text-success mb-2">${{ number_format($room['room_type']['price'], 2) }}</div>
                    <p class="text-muted mb-4">Per Month</p>

                    <span class="badge bg-{{ $room['status'] ? 'success' : 'secondary' }}">
                        {{ $room['status'] ? 'Available' : 'Unavailable' }}
                    </span>

                    <hr class="my-4">
                    <div class="text-muted"><i class="ti ti-ruler"></i> {{ $room['room_type']['room_size'] }}</div>
                    <div class="text-muted"><i class="ti ti-map-pin"></i>
                        {{ ucfirst($room['location']['location_name']) }}</div>
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="ti ti-info-circle me-2"></i>Room Information</h5>
                    <p class="mb-0">{{ $room['description'] ?: 'No description available.' }}</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-2"><i class="ti ti-calendar me-2"></i>Created At
                            </h6>
                            <div>{{ \Carbon\Carbon::parse($room['created_at'])->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-2"><i class="ti ti-refresh me-2"></i>Last Updated
                            </h6>
                            <div>{{ \Carbon\Carbon::parse($room['updated_at'])->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clients -->
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="ti ti-users me-2"></i>Clients</h5>
                    @if (empty($room['clients']))
                        <p class="text-muted mb-0">No clients assigned to this room.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($room['clients'] as $client)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $client['name'] }}</span>
                                    <span class="badge bg-primary">{{ $client['status'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
