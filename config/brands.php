<?php

return [
    'prompt_version' => 'brand-profile-v1',

    'defaults' => [
        'tone_preset' => 'balanced',
        'flow_preset' => 'balanced',
        'tense_preset' => 'balanced',
        'reading_level_preset' => 'balanced',
        'primary_color' => '#2E5BFF',
        'secondary_color' => '#00B8A9',
        'heading_font' => 'public_sans',
        'body_font' => 'public_sans',
    ],

    'limits' => [
        'max_terms' => 20,
        'max_term_length' => 60,
    ],

    'presets' => [
        'tone' => [
            'formal' => [
                'label' => 'Formal',
                'description' => 'Protocol-first and administrative.',
                'prompt_file' => 'tone-formal.md',
            ],
            'balanced' => [
                'label' => 'Balanced',
                'description' => 'Clear, capable, and human.',
                'prompt_file' => 'tone-balanced.md',
            ],
            'informal' => [
                'label' => 'Informal',
                'description' => 'Peer-level and conversational.',
                'prompt_file' => 'tone-informal.md',
            ],
        ],
        'flow' => [
            'descriptive' => [
                'label' => 'Descriptive',
                'description' => 'Focused on each item and its meaning.',
                'prompt_file' => 'flow-descriptive.md',
            ],
            'balanced' => [
                'label' => 'Balanced',
                'description' => 'Structured with a natural through-line.',
                'prompt_file' => 'flow-balanced.md',
            ],
            'narrative' => [
                'label' => 'Narrative',
                'description' => 'A flowing story from context to next step.',
                'prompt_file' => 'flow-narrative.md',
            ],
        ],
        'tense' => [
            'serious' => [
                'label' => 'Serious',
                'description' => 'Direct, focused, and matter-of-fact.',
                'prompt_file' => 'tense-serious.md',
            ],
            'balanced' => [
                'label' => 'Balanced',
                'description' => 'Calm and straightforward without feeling stark.',
                'prompt_file' => 'tense-balanced.md',
            ],
            'relaxed' => [
                'label' => 'Relaxed',
                'description' => 'Easygoing and unforced.',
                'prompt_file' => 'tense-relaxed.md',
            ],
        ],
        'reading_level' => [
            'simpler' => [
                'label' => 'Simpler',
                'description' => 'Plain words and short sentences.',
                'prompt_file' => 'reading-level-simpler.md',
            ],
            'balanced' => [
                'label' => 'Balanced',
                'description' => 'Natural professional readability.',
                'prompt_file' => 'reading-level-balanced.md',
            ],
            'complex' => [
                'label' => 'Complex',
                'description' => 'Rich vocabulary for an experienced reader.',
                'prompt_file' => 'reading-level-complex.md',
            ],
        ],
    ],

    'fonts' => [
        'public_sans' => [
            'label' => 'Public Sans',
            'stack' => '"Public Sans", "Segoe UI", sans-serif',
        ],
        'space_grotesk' => [
            'label' => 'Space Grotesk',
            'stack' => '"Space Grotesk", "Avenir Next", sans-serif',
        ],
        'source_serif' => [
            'label' => 'Source Serif',
            'stack' => '"Source Serif 4", Georgia, serif',
        ],
        'ibm_plex_sans' => [
            'label' => 'IBM Plex Sans',
            'stack' => '"IBM Plex Sans", "Segoe UI", sans-serif',
        ],
    ],
];
