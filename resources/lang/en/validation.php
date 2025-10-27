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
];
