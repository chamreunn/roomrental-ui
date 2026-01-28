@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-vcenter table-striped">
                <thead>
                    <tr>
                        <th>{{ __('roomtype.name') }}</th>
                        <th>{{ __('roomtype.size') }}</th>
                        <th>{{ __('roomtype.price') }}</th>
                        <th>{{ __('roomtype.description') }}</th>
                        {{-- <th>{{ __('titles.created_at') }}</th>
                        <th>{{ __('titles.updated_at') }}</th> --}}
                        <th>{{ __('titles.status') }}</th>
                        <th>{{ __('titles.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roomtypes as $roomtype)
                        <tr>
                            <td>{{ $roomtype['type_name'] }}</td>
                            <td>{{ $roomtype['room_size'] }}</td>
                            <td>{{ $roomtype['price'] }}</td>
                            <td>{{ $roomtype['description'] ?? '-' }}</td>
                            {{-- <td>{{ $roomtype['create_date_kh'] }}</td>
                            <td>{{ $roomtype['update_date_kh'] }}</td> --}}
                            <td><span
                                    class="{{ $roomtype['status_badge']['class'] }}">{{ __($roomtype['status_badge']['name']) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('roomtype.show', $roomtype['id']) }}" class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="tooltip" title="{{ __('titles.edit') }}" data-bs-placement="auto">
                                    <x-icon name="edit" class="me-0" />
                                </a>
                                <button type="button" data-bs-toggle="modal" data-bs-target="#{{ $roomtype['id'] }}"
                                    class="btn btn-sm btn-outline-danger">
                                    <x-icon name="trash" class="icon me-0" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">{{ __('titles.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($roomtypes->hasPages())
            <div class="card-footer">
                {{ $roomtypes->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    @foreach ($roomtypes as $roomtype)
        <div class="modal modal-blur fade" id="{{ $roomtype['id'] }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <form action="{{ route('roomtype.destroy', $roomtype['id']) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="modal-title">{{ __('modal.confirm_title') }}</div>
                            <div>{{ __('modal.confirm_message') }}</div>
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
    @endforeach
@endsection
