<?php

return [
    // Field Labels
    'name' => 'Room Type Name',
    'size' => 'Room Size',
    'price' => 'Price',
    'description' => 'Description',
    'save' => 'Save',
    'select_roomtype' => 'Select room type',
    'updated_successfully' => 'Room type updated successfully.',
    'update_failed' => 'Failed to update room type.',

    // Validation Messages
    'name_required' => 'The room type name field is required.',
    'name_string' => 'The room type name must be a valid string.',
    'name_max' => 'The room type name may not be greater than 255 characters.',

    'size_required' => 'The room size field is required.',
    'size_string' => 'The room size must be a valid string.',
    'size_max' => 'The room size may not be greater than 100 characters.',

    'price_required' => 'The price field is required.',
    'price_numeric' => 'The price must be a number.',
    'price_min' => 'The price must be at least 0.',

    'description_string' => 'The description must be a valid string.',
    'description_max' => 'The description may not be greater than 1000 characters.',
];
