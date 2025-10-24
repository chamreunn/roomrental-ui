@extends('layouts.app')

@section('content')
    <div class="card">
        <form action="{{ route('roomtype.update',$roomtype['id']) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-4">
                        <label for="roomtype_name" class="form-label required">{{ __('roomtype.name') }}</label>
                        <input type="text" id="roomtype_name" name="name" class="form-control"
                            value="{{ old('roomtype_name', $roomtype['type_name']) }}"
                            placeholder="{{ __('roomtype.name') }}" autofocus>
                        @error('name')
                            <div class="text-red mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-4">
                        <label for="roomtype_size" class="form-label required">{{ __('roomtype.size') }}</label>
                        <input type="text" id="roomtype_size" name="size" class="form-control"
                            value="{{ old('roomtype_size', $roomtype['room_size']) }}"
                            placeholder="{{ __('roomtype.size') }}">
                        @error('size')
                            <div class="text-red mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-4">
                        <label for="roomtype_price" class="form-label required">{{ __('roomtype.price') }}</label>
                        <input type="text" id="roomtype_price" name="price" class="form-control"
                            value="{{ old('roomtype_price', $roomtype['price']) }}"
                            placeholder="{{ __('roomtype.price') }}">
                        @error('price')
                            <div class="text-red mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-12">
                        <label for="roomtype_description" class="form-label">{{ __('roomtype.description') }}</label>
                        <textarea name="description" class="form-control" id="roomtype_description"
                            placeholder="{{ __('roomtype.description') }}">{{ old('roomtype_description', $roomtype['description']) }}</textarea>
                        @error('description')
                            <div class="text-red mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ __('roomtype.save') }}</button>
            </div>
        </form>
    </div>
@endsection
