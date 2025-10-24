@extends('layouts.app')

@section('content')
    <div class="card">
        <form action="{{ route('location.update', $location['id']) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-4">
                        <label for="location_name" class="form-label required">{{ __('location.name') }}</label>
                        <input type="text" id="location_name" name="location_name" class="form-control"
                            value="{{ old('location_name', $location['location_name'])  }}"
                            placeholder="{{ __('location.name') }}" autocomplete="off" autofocus>
                        @error('location_name')
                            <div class="text-red mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-4">
                        <label for="location_address" class="form-label required">{{ __('location.address') }}</label>
                        <input type="text" id="location_address" name="location_address" class="form-control"
                            value="{{ old('location_address', $location['address']) }}"
                            placeholder="{{ __('location.address') }}" autocomplete="off">
                        @error('location_address')
                            <div class="text-red mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-4">
                        <label for="status" class="form-label">{{ __('titles.status') }}</label>
                        <select name="is_active" id="status" class="form-select tom-select">
                            <option value="">{{ __('account.select_a_status') }}</option>
                            @foreach ($statusses as $statusKey => $status)
                                <option value="{{ $statusKey }}" {{ old('status', $location['is_active'] ?? '') == $statusKey ? 'selected' : '' }}
                                    data-custom-properties="<span class='{{ $status['class'] }} badge mx-0'>{{ __($status['name']) }}</span>">
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="text-red mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-12">
                        <label for="location_description" class="form-label">{{ __('location.description') }}</label>
                        <textarea name="location_description" class="form-control" id="location_description"
                            placeholder="{{ __('location.description') }}">{{ old('location_description', $location['description']) }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ __('titles.save_update') }}</button>
            </div>
        </form>
    </div>
@endsection
