<?php

return [
    'name' => 'Room Type Name',
    'size' => 'Room Size',
    'price' => 'Room Price',
    'description' => 'Description',
    'save' => 'Save',

    // Validation messages
    'name_required' => 'The room type name is required.',
    'name_string' => 'The room type name must be text.',
    'name_max' => 'The room type name may not exceed 255 characters.',

    'size_required' => 'The room size is required.',
    'size_string' => 'The room size must be text.',
    'size_max' => 'The room size may not exceed 100 characters.',

    'price_required' => 'The price is required.',
    'price_numeric' => 'The price must be a valid number.',
    'price_min' => 'The price must be greater than or equal to 0.',

    'description_string' => 'The description must be text.',
    'description_max' => 'The description may not exceed 1000 characters.',

    // Response messages
    'created_successfully' => 'Room type created successfully.',
    'create_failed' => 'Failed to create room type.',
];
