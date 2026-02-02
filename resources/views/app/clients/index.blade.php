@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <form method="GET" class="row g-2 align-items-end w-100">

                {{-- Search --}}
                <div class="col-md-5">
                    <label class="form-label">{{ __('client.search') ?? 'Search' }}</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                        placeholder="{{ __('client.search_placeholder') ?? 'Name / Email / Phone / Room' }}">
                </div>

                {{-- Room Status --}}
                <div class="col-md-4">
                    <label class="form-label">{{ __('room.status') ?? 'Room Status' }}</label>
                    <select name="room_status" class="form-select tom-select">
                        <option value="">{{ __('client.select') ?? 'All' }}</option>
                        @foreach ($roomStatuses as $key => $st)
                            <option value="{{ $key }}" @selected((string) request('room_status') === (string) $key)
                                data-custom-properties="<span class='{{ $st['class'] }} badge mx-0'>{{ __($st['name']) }}</span>">
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary w-100" type="submit">
                        {{ __('invoice.filter') ?? 'Filter' }}
                    </button>

                    {{-- ✅ Reset must include locationId --}}
                    <a class="btn btn-secondary w-100" href="{{ route('clients.clients', $locationId) }}">
                        {{ __('invoice.reset') ?? 'Reset' }}
                    </a>
                </div>

            </form>
        </div>

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
                        <th>{{ __('client.status') }}</th>
                        <th class="text-end">{{ __('client.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm"
                                        style="background-image: url('{{ api_image($client['image'] ?? null) }}')">
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
                                {{ $client['room']['building_name'] ?? '-' }} /
                                {{ $client['room']['room_name'] ?? '-' }}
                            </td>

                            <td>{{ $client['start_rental_date'] ?? '-' }}</td>
                            <td>{{ $client['end_rental_date'] ?? '-' }}</td>

                            <td>
                                @php($st = $client['room']['status_meta'] ?? null)
                                <span class="{{ $st['badge'] ?? 'badge bg-secondary-lt' }}">
                                    {{ __($st['name'] ?? 'status.unknown') }}
                                </span>
                            </td>

                            <td class="text-end">
                                {{-- Only show room button if room exists --}}
                                @if (!empty($client['room']['id']))
                                    <a href="{{ route('room.show', [$client['room']['id'], $client['room']['location_id']]) }}"
                                        class="btn btn-sm btn-info">
                                        <x-icon name="eye" class="me-0" />
                                    </a>
                                @endif

                                <a href="{{ route('clients.edit', [$client['id'], $client['room']['location_id'] ?? $locationId]) }}"
                                    class="btn btn-sm btn-warning">
                                    <x-icon name="edit" class="me-0" />
                                </a>

                                <a href="#" data-bs-toggle="modal"
                                    data-bs-target="#deleteClientModal-{{ $client['id'] }}" class="btn btn-sm btn-danger">
                                    <x-icon name="trash" class="me-0" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                <x-empty-state title="{{ __('cash_transaction.no_data') }}"
                                    message="{{ __('cash_transaction.no_data') }}" svg="svgs/no_result.svg"
                                    width="450px" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Delete Modals --}}
        @foreach ($clients as $client)
            <div class="modal modal-blur fade" id="deleteClientModal-{{ $client['id'] }}" tabindex="-1"
                aria-hidden="true">
                <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                    <div class="modal-content">

                        <div class="modal-body">
                            <div class="modal-title">{{ __('modal.confirm_title') ?? 'Are you sure?' }}</div>
                            <div>
                                {!! __('modal.confirm_delete_client', [
                                    'name' => '<span class="text-primary fw-bold">' . ($client['username'] ?? '-') . '</span>',
                                ]) !!}
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">
                                {{ __('modal.cancel') ?? 'Cancel' }}
                            </button>

                            {{-- ✅ Delete form --}}
                            <form
                                action="{{ route('clients.destroy', [$client['id'], $client['room']['location_id'] ?? $locationId]) }}"
                                method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    {{ __('common.button_delete') ?? 'Delete' }}
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        @endforeach


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
