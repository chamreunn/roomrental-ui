<div class="card mb-3">
    <div class="card-header">
        <div>
            <h3 class="card-title">
                <x-icon name="users-plus" />
                {{ __('Sub Tenants') }}
            </h3>
            <div class="text-secondary small">
                {{ __('Add family members, roommates, or other people staying with the main tenant.') }}
            </div>
        </div>

        <div class="card-actions">
            <button type="button" class="btn btn-outline-primary btn-sm" id="addSubclientBtn">
                <x-icon name="plus" />
                {{ __('Add Sub Tenant') }}
            </button>
        </div>
    </div>

    <div class="card-body">
        <div id="deletedSubclientsWrapper"></div>

        <div id="subclientsWrapper">
            @foreach ($subclients as $index => $subclient)
                <div class="card mb-3 subclient-item">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Sub Tenant') }}</h3>

                        <div class="card-actions">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-subclient"
                                data-existing-id="{{ $subclient['id'] ?? '' }}">
                                <x-icon name="trash" />
                                {{ __('Remove') }}
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <input type="hidden" data-name="id" name="subclients[{{ $index }}][id]"
                            value="{{ $subclient['id'] ?? '' }}">

                        <div class="row g-3">
                            <div class="col-lg-4">
                                <label class="form-label required">{{ __('tenant.full_name') }}</label>
                                <input type="text" data-name="username"
                                    name="subclients[{{ $index }}][username]" class="form-control"
                                    value="{{ $subclient['username'] ?? '' }}">
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label required">{{ __('tenant.date_of_birth') }}</label>
                                <input type="text" data-name="date_of_birth"
                                    name="subclients[{{ $index }}][date_of_birth]"
                                    class="form-control datepicker subclient-datepicker"
                                    value="{{ $subclient['date_of_birth'] ?? '' }}" autocomplete="off">
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label required">{{ __('tenant.gender') }}</label>
                                <select data-name="gender" name="subclients[{{ $index }}][gender]"
                                    class="form-select">
                                    <option value="">{{ __('tenant.select_option') }}</option>
                                    <option value="m" @selected(($subclient['gender_mapped'] ?? ($subclient['gender'] ?? '')) === 'm')>
                                        {{ __('tenant.male') }}
                                    </option>
                                    <option value="f" @selected(($subclient['gender_mapped'] ?? ($subclient['gender'] ?? '')) === 'f')>
                                        {{ __('tenant.female') }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label">{{ __('tenant.phone_number') }}</label>
                                <input type="text" data-name="phone_number"
                                    name="subclients[{{ $index }}][phone_number]" class="form-control"
                                    value="{{ $subclient['phone_number'] ?? '' }}">
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label">{{ __('tenant.email_address') }}</label>
                                <input type="email" data-name="email" name="subclients[{{ $index }}][email]"
                                    class="form-control" value="{{ $subclient['email'] ?? '' }}">
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label">{{ __('tenant.national_id') }}</label>
                                <input type="text" data-name="national_id"
                                    name="subclients[{{ $index }}][national_id]" class="form-control"
                                    value="{{ $subclient['national_id'] ?? '' }}">
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label">{{ __('tenant.passport') }}</label>
                                <input type="text" data-name="passport"
                                    name="subclients[{{ $index }}][passport]" class="form-control"
                                    value="{{ $subclient['passport'] ?? '' }}">
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label">{{ __('Photo') }}</label>
                                <input type="file" data-name="sub_client_image"
                                    name="subclients[{{ $index }}][sub_client_image]" class="form-control"
                                    accept="image/*">
                            </div>

                            <div class="col-lg-12">
                                <label class="form-label required">{{ __('tenant.current_address') }}</label>
                                <textarea data-name="address" name="subclients[{{ $index }}][address]" class="form-control">{{ $subclient['address'] ?? '' }}</textarea>
                            </div>

                            <div class="col-lg-12">
                                <label class="form-label">{{ __('tenant.description') }}</label>
                                <textarea data-name="description" name="subclients[{{ $index }}][description]" class="form-control">{{ $subclient['description'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="empty" id="subclientEmptyState">
            <div class="empty-icon">
                <x-icon name="users" />
            </div>
            <p class="empty-title">{{ __('No sub tenants added') }}</p>
            <p class="empty-subtitle text-secondary">
                {{ __('Use the add button if another person will stay in this room.') }}
            </p>
        </div>
    </div>
</div>

<template id="subclientTemplate">
    <div class="card mb-3 subclient-item">
        <div class="card-header">
            <h3 class="card-title">{{ __('Sub Tenant') }}</h3>

            <div class="card-actions">
                <button type="button" class="btn btn-outline-danger btn-sm remove-subclient">
                    <x-icon name="trash" />
                    {{ __('Remove') }}
                </button>
            </div>
        </div>

        <div class="card-body">
            <input type="hidden" data-name="id" value="">

            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="form-label required">{{ __('tenant.full_name') }}</label>
                    <input type="text" data-name="username" class="form-control">
                </div>

                <div class="col-lg-4">
                    <label class="form-label required">{{ __('tenant.date_of_birth') }}</label>
                    <input type="text" data-name="date_of_birth"
                        class="form-control datepicker subclient-datepicker" autocomplete="off">
                </div>

                <div class="col-lg-4">
                    <label class="form-label required">{{ __('tenant.gender') }}</label>
                    <select data-name="gender" class="form-select">
                        <option value="">{{ __('tenant.select_option') }}</option>
                        <option value="m">{{ __('tenant.male') }}</option>
                        <option value="f">{{ __('tenant.female') }}</option>
                    </select>
                </div>

                <div class="col-lg-4">
                    <label class="form-label">{{ __('tenant.phone_number') }}</label>
                    <input type="text" data-name="phone_number" class="form-control">
                </div>

                <div class="col-lg-4">
                    <label class="form-label">{{ __('tenant.email_address') }}</label>
                    <input type="email" data-name="email" class="form-control">
                </div>

                <div class="col-lg-4">
                    <label class="form-label">{{ __('tenant.national_id') }}</label>
                    <input type="text" data-name="national_id" class="form-control">
                </div>

                <div class="col-lg-4">
                    <label class="form-label">{{ __('tenant.passport') }}</label>
                    <input type="text" data-name="passport" class="form-control">
                </div>

                <div class="col-lg-4">
                    <label class="form-label">{{ __('Photo') }}</label>
                    <input type="file" data-name="sub_client_image" class="form-control" accept="image/*">
                </div>

                <div class="col-lg-12">
                    <label class="form-label required">{{ __('tenant.current_address') }}</label>
                    <textarea data-name="address" class="form-control"></textarea>
                </div>

                <div class="col-lg-12">
                    <label class="form-label">{{ __('tenant.description') }}</label>
                    <textarea data-name="description" class="form-control"></textarea>
                </div>
            </div>
        </div>
    </div>
</template>
