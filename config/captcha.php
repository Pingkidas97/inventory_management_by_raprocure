<?php

return [
    'disable' => env('CAPTCHA_DISABLE', false),

    // Only numeric characters for all captchas
    'characters' => ['0','1','2','3','4','5','6','7','8','9'],

    'default' => [
        'length' => 4,
        'width' => 120,
        'height' => 36,
        'quality' => 90,
        'math' => false,
        'expire' => 1440, // 24 hours = 24 * 60 = 1440 minutes
        'encrypt' => false,
    ],

    'math' => [
        'length' => 9,
        'width' => 120,
        'height' => 36,
        'quality' => 90,
        'math' => true,
    ],

    'flat' => [
        'length' => 4,                        // Show only 4 characters
        'width' => 120,
        'height' => 36,
        'quality' => 90,
        'lines' => 0,                         // No noise lines
        'bgImage' => false,                   // No background image
        'bgColor' => '#ffffff',              // White background
        'fontColors' => ['#000000'],         // Black text
        'contrast' => 0,
    ],

    'mini' => [
        'length' => 3,
        'width' => 60,
        'height' => 32,
    ],

    'inverse' => [
        'length' => 5,
        'width' => 120,
        'height' => 36,
        'quality' => 90,
        'sensitive' => true,
        'angle' => 12,
        'sharpen' => 10,
        'blur' => 2,
        'invert' => true,
        'contrast' => -5,
    ]
];
