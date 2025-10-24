@extends('layouts.app')

@section('content')
    <form action="{{ route('room.store', $locationId) }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row g-3">

                    {{-- Building Name --}}
                    <div class="col-lg-3 col-md-6">
                        <label for="building_name" class="form-label required">
                            {{ __('room.building_name') }}
                        </label>
                        <input type="text" name="building_name" id="building_name"
                            class="form-control @error('building_name') is-invalid @enderror"
                            placeholder="{{ __('room.building_name') }}" value="{{ old('building_name') }}" autofocus>
                        @error('building_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Floor Name --}}
                    <div class="col-lg-3 col-md-6">
                        <label for="floor_name" class="form-label required">
                            {{ __('room.floor_name') }}
                        </label>
                        <input type="text" name="floor_name" id="floor_name"
                            class="form-control @error('floor_name') is-invalid @enderror"
                            placeholder="{{ __('room.floor_name') }}" value="{{ old('floor_name') }}">
                        @error('floor_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Room Name --}}
                    <div class="col-lg-3 col-md-6">
                        <label for="room_name" class="form-label required">
                            {{ __('room.name') }}
                        </label>
                        <input type="text" name="room_name" id="room_name"
                            class="form-control @error('room_name') is-invalid @enderror"
                            placeholder="{{ __('room.name') }}" value="{{ old('room_name') }}">
                        @error('room_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Room Type --}}
                    <div class="col-lg-3 col-md-6">
                        <label for="room_type_id" class="form-label required">
                            {{ __('roomtype.name') }}
                        </label>
                        <select name="room_type_id" id="room_type_id"
                            class="form-select tom-select @error('room_type_id') is-invalid @enderror">
                            <option value="">{{ __('roomtype.select_roomtype') }}</option>
                            @foreach ($roomtypes as $roomtype)
                                <option value="{{ $roomtype['id'] }}"
                                    {{ old('room_type_id') == $roomtype['id'] ? 'selected' : '' }}>
                                    {{ $roomtype['type_name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('room_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="col-lg-12">
                        <label for="description" class="form-label">
                            {{ __('room.description') }}
                        </label>
                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                            placeholder="{{ __('room.description') }}">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">
                    {{ __('room.save') }}
                </button>
            </div>
        </div>
    </form>
@endsection
