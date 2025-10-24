@extends('layouts.app')

@section('content')
    <div class="card">
        <form action="{{ route('location.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <label for="location_name" class="form-label required">{{ __('location.name') }}</label>
                        <input type="text" id="location_name" name="location_name" class="form-control"
                            value="{{ old('location_name') }}" placeholder="{{ __('location.name') }}" autocomplete="off"
                            autofocus>
                        @error('location_name')
                            <div class="text-red mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <label for="location_address" class="form-label required">{{ __('location.address') }}</label>
                        <input type="text" id="location_address" name="location_address" class="form-control"
                            value="{{ old('location_address') }}" placeholder="{{ __('location.address') }}"
                            autocomplete="off">
                        @error('location_address')
                            <div class="text-red mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-12">
                        <label for="location_description" class="form-label">{{ __('location.description') }}</label>
                        <textarea name="location_description" class="form-control" id="location_description"
                            placeholder="{{ __('location.description') }}">{{ old('location_description') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ __('location.save') }}</button>
            </div>
        </form>
    </div>
@endsection
