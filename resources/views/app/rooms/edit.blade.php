@extends('layouts.app')

@section('content')
    <div class="row row-cards">
        <div class="col-lg-8">
            <form action="{{ route('room.update', ['room_id' => $room['id'], 'location_id' => $locationId]) }}" method="POST">
                @csrf
                @method('PATCH')
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
                                    placeholder="{{ __('room.building_name') }}"
                                    value="{{ old('building_name', $room['building_name'] ?? '') }}" autofocus>
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
                                    placeholder="{{ __('room.floor_name') }}"
                                    value="{{ old('floor_name', $room['floor_name'] ?? '') }}">
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
                                    placeholder="{{ __('room.name') }}"
                                    value="{{ old('room_name', $room['room_name'] ?? '') }}">
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
                                            {{ old('room_type_id', $room['room_type_id'] ?? '') == $roomtype['id'] ? 'selected' : '' }}>
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
                                    placeholder="{{ __('room.description') }}">{{ old('description', $room['description'] ?? '') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-{{ $color }}">
                            {{ __('room.save') }}<x-icon name="refresh" class="icon-end" />
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-{{ $color }} text-{{ $color }}-fg">
                    <h3 class="card-title">{{ __('roomtype.name') }}</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">{{ __('roomtype.name') }}</div>
                            <div class="datagrid-content">{{ $room['room_type']['type_name'] }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">{{ __('roomtype.size') }}</div>
                            <div class="datagrid-content">{{ $room['room_type']['room_size'] }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">{{ __('roomtype.price') }}</div>
                            <div class="datagrid-content">{{ $room['room_type']['price'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
