@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="list-group list-group-flush">
            @foreach ($locations as $location)
                <a href="{{ route('user_income.list', $location['id']) }}"
                    class="list-group-item list-group-item-action d-flex align-items-center">
                    <span class="avatar avatar-1 bg-primary-lt me-2">
                        <x-icon name="map-pin" />
                    </span>
                    <div class="flex-fill">
                        <div class="fw-bold">{{ $location['location_name'] }}</div>
                        <div class="text-muted small">{{ $location['address'] ?? __('room.no_address') }}</div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endsection
