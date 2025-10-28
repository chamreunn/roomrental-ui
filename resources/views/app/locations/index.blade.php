@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-vcenter table-striped">
                <thead>
                    <tr>
                        <th>{{ __('location.name') }}</th>
                        <th>{{ __('location.address') }}</th>
                        <th>{{ __('location.description') }}</th>
                        <th>{{ __('titles.created_at') }}</th>
                        <th>{{ __('titles.updated_at') }}</th>
                        <th>{{ __('titles.status') }}</th>
                        <th>{{ __('titles.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($locations as $location)
                        <tr>
                            <td>{{ $location['location_name'] }}</td>
                            <td>{{ $location['address'] }}</td>
                            <td>{{ $location['description'] }}</td>
                            <td>{{ $location['create_date_kh'] }}</td>
                            <td>{{ $location['update_date_kh'] }}</td>
                            <td><span
                                    class="{{ $location['status_badge']['class'] }}">{{ __($location['status_badge']['name']) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('location.edit', $location['id']) }}"
                                    class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip"
                                    title="{{ __('titles.edit') }}" data-bs-placement="auto">
                                    <x-icon name="edit" class="me-0" />
                                </a>
                                <button type="button" data-bs-toggle="modal" data-bs-target="#{{ $location['id'] }}"
                                    class="btn btn-sm btn-outline-danger">
                                    <x-icon name="trash" class="icon me-0" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                <x-empty-state title="{{ __('titles.no_room_found') }}"
                                    message="{{ __('titles.please_find_another_location') }}" svg="svgs/no_result.svg"
                                    width="450px" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($locations->hasPages())
            <div class="card-footer">
                {{ $locations->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    @foreach ($locations as $location)
        <div class="modal modal-blur fade" id="{{ $location['id'] }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <form action="{{ route('location.destroy', $location['id']) }}" method="POST">
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
