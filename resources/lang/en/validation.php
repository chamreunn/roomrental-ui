<?php

return [
    'required' => 'The :attribute field is required.',
    'string'   => 'The :attribute must be a string.',
    'max'      => [
        'string' => 'The :attribute may not be greater than :max characters.',
    ],
    'email'    => 'The :attribute must be a valid email address.',
    'unique'   => 'The :attribute has already been taken.',
    'in'       => 'The selected :attribute is invalid.',
    'exists'   => 'The selected :attribute is invalid.',
    'image'    => 'The :attribute must be an image.',
    'mimes'    => 'The :attribute must be a file of type: :values.',
    'date_format' => 'The :attribute does not match the format :format.',
    'confirmed'   => 'The :attribute confirmation does not match.',

    // create account validation
    'name_required' => 'Name is required.',
    'name_string' => 'Name must be a string.',
    'name_max' => 'Name cannot exceed 255 characters.',

    'role_required' => 'Role is required.',

    'email_required' => 'Email is required.',

    'phone_required' => 'Phone number is required.',
    'phone_string' => 'Phone number must be a string.',
    'phone_max' => 'Phone number cannot exceed 20 characters.',

    // 'dob_required' => 'Date of birth is required.',
    'dob_format' => 'Date of birth must be in the format dd-mm-yyyy.',

    'password_required' => 'Password is required.',
    'password_string' => 'Password must be a string.',

    'address_string' => 'Address must be a string.',
    // 'address_max' => 'Address cannot exceed 500 characters.',

    'profile_picture_image' => 'Profile picture must be an image.',
    'profile_picture_mimes' => 'Profile picture must be a file of type: jpeg, png, jpg, gif, webp.',
    'profile_picture_max' => 'Profile picture cannot exceed 2MB.',

    'username_required' => 'Please enter the client name.',
    'username_max' => 'The name must not exceed 100 characters.',

    'gender_required' => 'Please select gender.',
    'gender_in' => 'Gender must be either Male or Female.',

    'phone_number_required' => 'Please enter phone number.',
    'phone_number_max' => 'Phone number must not exceed 20 characters.',

    'email_email' => 'Invalid email address.',
    'email_max' => 'Email must not exceed 100 characters.',

    'dob_required' => 'Please select date of birth.',
    // 'dob_format' => 'Invalid date format. Use dd-mm-yyyy.',

    'start_date_invalid' => 'Invalid start date.',
    'end_date_invalid' => 'Invalid end date.',

    'national_id_max' => 'National ID must not exceed 30 characters.',
    'passport_max' => 'Passport number must not exceed 30 characters.',

    'address_required' => 'Please enter your current address.',
    'address_max' => 'Address must not exceed 255 characters.',

    'image_type' => 'The uploaded file must be an image.',
    'image_max' => 'Image size must not exceed 2MB.',

    'description_max' => 'Description must not exceed 255 characters.',

    'custom' => [
        'username' => [
            'required' => 'Please enter the client name.',
            'max'      => 'Client name may not be greater than :max characters.',
        ],
        'gender' => [
            'required' => 'Please select a gender.',
            'in'       => 'The selected gender is invalid.',
        ],
        'phone_number' => [
            'required' => 'Please enter a phone number.',
            'max'      => 'Phone number may not exceed :max characters.',
        ],
        'email' => [
            'email' => 'Please enter a valid email address.',
            'max'   => 'Email may not exceed :max characters.',
        ],
        'dob' => [
            'required'    => 'Please enter the date of birth.',
            'date_format' => 'Date of birth must be in dd-mm-YYYY format.',
        ],
        'national_id' => [
            'max' => 'National ID may not exceed :max characters.',
        ],
        'passport' => [
            'max' => 'Passport number may not exceed :max characters.',
        ],
        'address' => [
            'required' => 'Please enter the address.',
            'max'      => 'Address may not exceed :max characters.',
        ],
        'image' => [
            'image' => 'The file must be an image.',
            'max'   => 'The image size may not be greater than 2MB.',
        ],
        'start_rental_date' => [
            'required' => 'Please enter the rental start date.',
            'date'     => 'Start rental date must be a valid date.',
        ],
        'end_rental_date' => [
            'date' => 'End rental date must be a valid date.',
        ],
        'description' => [
            'max' => 'Description may not exceed :max characters.',
        ],
    ],

    'required_month' => 'The month field is required.',
    'required_old_electric' => 'Please enter the previous electric reading.',
    'required_new_electric' => 'Please enter the new electric reading.',
    'required_electric_rate' => 'Please enter the electric rate.',
    'required_old_water' => 'Please enter the previous water reading.',
    'required_new_water' => 'Please enter the new water reading.',
    'required_water_rate' => 'Please enter the water rate.',
    'new_electric_must_be_greater' => 'The new electric reading must be greater than the old reading.',
    'new_water_must_be_greater' => 'The new water reading must be greater than the old reading.',

    // 'custom' validation messages
    'no_permission' => 'You do not have permission to access this page.',
];
