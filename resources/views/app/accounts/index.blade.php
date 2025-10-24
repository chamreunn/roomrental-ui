@extends('layouts.app')

@section('content')

    {{-- Users grid --}}
    <div class="row row-cards mb-3">
        @forelse ($users as $user)
            <div class="col-md-6 col-lg-3">
                <div class="card user-card">
                    <div class="card-body p-4 text-center">
                        <span class="avatar avatar-xl mb-3"
                            style="background-image: url('{{ $user['user_profile'] ?? '/static/avatars/default.jpg' }}')">
                        </span>
                        <h3 class="m-0 mb-1 text-primary"><a href="#">{{ ucfirst($user['name']) }}</a></h3>
                        <div class="text-secondary">{{ ucfirst($user['email'] ?? '') }}</div>
                        <div class="mt-3">
                            <span class="{{ $user['role_badge']['class'] }}">{{ ucfirst($user['role'] ?? '') ?? '' }}</span>
                        </div>
                    </div>

                    <div class="d-flex">
                        <a href="{{ route('account.show', $user['id']) }}" class="card-btn text-primary">
                            <x-icon name="edit" />
                            <span class="mx-1"> {{ __('titles.show') . " / " . __('titles.edit') }}</span>
                        </a>
                        {{-- <a href="#" class="card-btn text-red">
                            <x-icon name="trash" />
                            <span class="mx-1">{{ __('titles.delete') }}</span>
                        </a> --}}
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <x-empty-state title="{{ __('titles.no_users_found') }}"
                    message="{{ __('titles.try_adjust_search_or_create') }}" :action="[
                    'text' => __('titles.create') . __('titles.account'),
                    'url' => route('account.create'),
                    'class' => 'btn btn-primary',
                    'icon' => 'plus',
                ]" svg="svgs/no_result.svg" width="450px" />
            </div>
        @endforelse
    </div>

    @if ($users->hasPages())
        <div class="card-footer">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    @endif

@endsection
