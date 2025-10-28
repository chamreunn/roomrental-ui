@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">

            @if (!empty($userLocations) && count($userLocations) > 0)
                <div class="table-responsive">
                    <table class="table table-vcenter table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('account.user_name') }}</th>
                                <th>{{ __('account.location_name') }}</th>
                                <th>{{ __('account.assigned_at') }}</th>
                                <th>{{ __('account.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($userLocations as $index => $userLocation)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $userLocation['user']['name'] ?? '-' }}</td>
                                    <td>{{ $userLocation['location']['location_name'] ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($userLocation['created_at'])->format('d-m-Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('account.show', $userLocation['user']['id']) }}"
                                            class="btn btn-sm btn-primary">
                                            {{ __('titles.edit') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-empty-state title="{{ __('titles.no_room_found') }}"
                    message="{{ __('titles.please_find_another_location') }}" svg="svgs/no_result.svg" width="450px" />
            @endif
        </div>
    </div>
@endsection
