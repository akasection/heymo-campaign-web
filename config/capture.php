<?php

return [
    'consent_policy_version' => 'email-consent-v1',
    'age_groups' => [
        '18_29' => [
            'label' => '18-29',
            'description' => 'Young adult',
            'min' => 18,
            'max' => 29,
        ],
        '30_44' => [
            'label' => '30-44',
            'description' => 'Adult',
            'min' => 30,
            'max' => 44,
        ],
        '45_59' => [
            'label' => '45-59',
            'description' => 'Midlife adult',
            'min' => 45,
            'max' => 59,
        ],
        '60_74' => [
            'label' => '60-74',
            'description' => 'Older adult',
            'min' => 60,
            'max' => 74,
        ],
        '75_plus' => [
            'label' => '75+',
            'description' => 'Older adult',
            'min' => 75,
            'max' => null,
        ],
    ],
    'limits' => [
        'preferred_name' => 80,
        'concern' => 1000,
        'per_ip_per_minute' => 10,
        'per_email_per_minute' => 3,
    ],
];
