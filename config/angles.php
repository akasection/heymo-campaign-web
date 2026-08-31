<?php

return [
    'limits' => [
        'name' => 120,
        'text' => 2000,
    ],

    'defaults' => [
        'tone' => 'matter_of_fact',
    ],

    'tones' => [
        'reassuring' => [
            'label' => 'Reassuring',
            'description' => 'Calm, supportive, and confidence-building.',
        ],
        'authoritative' => [
            'label' => 'Authoritative',
            'description' => 'Precise, evidence-led, and assured.',
        ],
        'warm' => [
            'label' => 'Warm',
            'description' => 'Human, encouraging, and approachable.',
        ],
        'matter_of_fact' => [
            'label' => 'Matter of fact',
            'description' => 'Direct, clear, and free of hype.',
        ],
    ],
];
