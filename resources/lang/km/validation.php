<?php

return [
    'required' => ':attribute គឺជាតំរូវការ',
    'string'   => ':attribute ត្រូវតែជាផ្សាយអក្សរ',
    'max'      => [
        'string' => ':attribute មិនអាចលើស :max តួអក្សរ',
    ],
    'email'    => ':attribute ត្រូវតែជាអ៊ីមែលត្រឹមត្រូវ',
    'unique'   => ':attribute ត្រូវបានប្រើរួចហើយ',
    'in'       => ':attribute ដែលបានជ្រើសមិនត្រឹមត្រូវ',
    'exists'   => ':attribute ដែលបានជ្រើសមិនត្រឹមត្រូវ',
    'image'    => ':attribute ត្រូវតែជារូបភាព',
    'mimes'    => ':attribute ត្រូវតែជាប្រភេទឯកសារ៖ :values',
    'date_format' => ':attribute មិនត្រូវតាមទ្រង់ទ្រាយ :format',
    'confirmed'   => 'ការបញ្ជាក់ :attribute មិនត្រូវគ្នា',

    // accounte validation 
    'name_required' => 'សូមបំពេញ​ឈ្មោះ។',
    'name_string' => 'ឈ្មោះត្រូវតែជាអក្សរ។',
    'name_max' => 'ឈ្មោះមិនអាចលើសពី ២៥៥ តួអក្សរ។',

    'role_required' => 'សូមបំពេញតួនាទី។',

    'email_required' => 'សូមបំពេញ អ៊ីមែល។',
    'email_email' => 'សូមផ្តល់អ៊ីមែលត្រឹមត្រូវ។',
    'email_max' => 'អ៊ីមែលមិនអាចលើសពី ២៥៥ តួអក្សរ។',

    'phone_required' => 'សូមបំពេញលេខទូរស័ព្ទ។',
    'phone_string' => 'លេខទូរស័ព្ទត្រូវតែជាអក្សរ។',
    'phone_max' => 'លេខទូរស័ព្ទមិនអាចលើសពី ២០ តួអក្សរ។',

    'dob_required' => 'សូមបំពេញថ្ងៃខែឆ្នាំកំណើត។',
    'dob_format' => 'ថ្ងៃខែឆ្នាំកំណើតត្រូវមានទ្រង់ទ្រាយ dd-mm-yyyy។',

    'password_required' => 'សូមបំពេញលេខសំងាត់។',
    'password_string' => 'លេខសំងាត់ត្រូវតែជាអក្សរ។',

    'address_string' => 'អាសយដ្ឋានត្រូវតែជាអក្សរ។',
    'address_max' => 'អាសយដ្ឋានមិនអាចលើសពី ៥០០ តួអក្សរ។',

    'profile_picture_image' => 'រូបភាពប профត្រូវតែជារូបភាព។',
    'profile_picture_mimes' => 'រូបភាពប профត្រូវតែជា jpeg, png, jpg, gif, webp។',
    'profile_picture_max' => 'រូបភាពប профមិនអាចលើស 2MB។',
];
