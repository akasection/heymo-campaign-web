<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sequence schedule
    |--------------------------------------------------------------------------
    |
    | Delay in seconds between generation and each sequence position. Position
    | 1 (promise) is sent immediately; later beats follow the configured cadence.
    | Production would use day-scale values; demo defaults are intentionally
    | short (under a minute apart) so the full sequence is observable in
    | Mailpit quickly.
    |
    */

    'schedule' => [
        1 => (int) env('DELIVERY_DELAY_POSITION_1', 0),
        2 => (int) env('DELIVERY_DELAY_POSITION_2', 30),
        3 => (int) env('DELIVERY_DELAY_POSITION_3', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Deterministic compliance language
    |--------------------------------------------------------------------------
    |
    | Appended by code to every campaign email. The model never writes this and
    | can never alter it. Per-brand compliance blocks are deferred; this single
    | global block satisfies the code-composed requirement for now.
    |
    */

    'compliance_text' => 'This message is informational and is not medical advice. It does not diagnose, treat, cure, or prevent any condition. Talk with a qualified clinician about your results and health decisions.',

    /*
    |--------------------------------------------------------------------------
    | Unsubscribe links
    |--------------------------------------------------------------------------
    |
    | The footer link is a temporary signed route; it remains valid for the
    | configured number of days. A shorter window reduces stale-link abuse.
    |
    */

    'unsubscribe' => [
        'expires_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Sign-off matrix
    |--------------------------------------------------------------------------
    |
    | Valediction family selected deterministically from the brand's tone and
    | tense presets (see ARCHITECTURE.md section 5). The signature identity is
    | derived from the brand name; the model never emits or overrides it.
    |
    */

    'sign_off' => [
        'formal' => [
            'serious' => 'Sincerely',
            'balanced' => 'Sincerely',
            'relaxed' => 'Best regards',
        ],
        'balanced' => [
            'serious' => 'Regards',
            'balanced' => 'Best regards',
            'relaxed' => 'Warm regards',
        ],
        'informal' => [
            'serious' => 'Thanks',
            'balanced' => 'Many thanks',
            'relaxed' => 'Take care',
        ],
    ],

];
