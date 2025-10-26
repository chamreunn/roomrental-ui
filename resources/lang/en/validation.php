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
    'email_email' => 'Please provide a valid email address.',
    'email_max' => 'Email cannot exceed 255 characters.',

    'phone_required' => 'Phone number is required.',
    'phone_string' => 'Phone number must be a string.',
    'phone_max' => 'Phone number cannot exceed 20 characters.',

    'dob_required' => 'Date of birth is required.',
    'dob_format' => 'Date of birth must be in the format dd-mm-yyyy.',

    'password_required' => 'Password is required.',
    'password_string' => 'Password must be a string.',

    'address_string' => 'Address must be a string.',
    'address_max' => 'Address cannot exceed 500 characters.',

    'profile_picture_image' => 'Profile picture must be an image.',
    'profile_picture_mimes' => 'Profile picture must be a file of type: jpeg, png, jpg, gif, webp.',
    'profile_picture_max' => 'Profile picture cannot exceed 2MB.',
];
