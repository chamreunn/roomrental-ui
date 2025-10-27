@extends('layouts.app')

@section('content')

    <div class="card">
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                    <tr>
                        <th>{{ __('client.username') }}</th>
                        <th>{{ __('client.gender') }}</th>
                        <th>{{ __('client.phone_number') }}</th>
                        <th>{{ __('client.room') }}</th>
                        <th>{{ __('client.start_rental_date') }}</th>
                        <th>{{ __('client.end_rental_date') }}</th>
                        <th class="text-end">{{ __('client.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm"
                                        style="background-image: url({{ asset($client['client_image'] ?? 'imgs/default-avatar.png') }})">
                                    </span>
                                    <div class="mx-2">
                                        <div class="text-primary fw-bold">{{ ucfirst($client['username'] ?? '-') }}</div>
                                        <span class="text-muted">{{ $client['email'] ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $client['gender'] ?? '-' }}</td>
                            <td>{{ $client['phone_number'] ?? '-' }}</td>
                            <td>
                                {{ $client['room']['building_name'] ?? '' }} /
                                {{ $client['room']['room_name'] ?? '' }}
                            </td>
                            <td>{{ $client['start_rental_date'] ?? '-' }}</td>
                            <td>{{ $client['end_rental_date'] ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('room.show', [$client['room']['id'], $client['room']['location_id']]) }}"
                                    class="btn btn-sm btn-info">
                                    <x-icon name="eye" class="me-0" />
                                </a>
                                <a href="{{ route('clients.edit', $client['id']) }}" class="btn btn-sm btn-warning">
                                    <x-icon name="edit" class="me-0" />
                                </a>
                                <form action="{{ route('clients.destroy', $client['id']) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('{{ __('client.confirm_delete') }}')">
                                        <x-icon name="trash" class="me-0" />
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                {{ __('client.no_clients_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="text-muted">
                {{ __('client.showing') }}
                {{ $clients->firstItem() ?? 0 }}–{{ $clients->lastItem() ?? 0 }}
                {{ __('client.of') }} {{ $clients->total() }}
                {{ __('client.entries') }}
            </div>
            <div>
                {{ $clients->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
