<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demographic presentation profiles
    |--------------------------------------------------------------------------
    |
    | Age group and sex map through a fixed lookup table to a presentation
    | profile. The profile is computed in code and passed to the model as
    | writing-style guidance only. It never becomes a basis for clinical
    | inference, and the exact age is never stored or reconstructed.
    |
    */

    'age_groups' => [
        '18_29' => [
            'register' => 'direct, plain vocabulary',
            'pacing' => 'short sentences and a quick rhythm',
            'reassurance' => 'low',
            'type_size' => 'standard',
            'density' => 'compact',
            'imagery' => 'light and energetic',
        ],
        '30_44' => [
            'register' => 'clear, everyday vocabulary',
            'pacing' => 'short-to-medium sentences with a steady rhythm',
            'reassurance' => 'moderate',
            'type_size' => 'standard',
            'density' => 'standard',
            'imagery' => 'practical and grounded',
        ],
        '45_59' => [
            'register' => 'measured, respectful vocabulary',
            'pacing' => 'medium sentences with room to breathe',
            'reassurance' => 'high',
            'type_size' => 'large',
            'density' => 'roomy',
            'imagery' => 'calm and patient',
        ],
        '60_74' => [
            'register' => 'plain, unhurried vocabulary',
            'pacing' => 'medium sentences with generous spacing',
            'reassurance' => 'high',
            'type_size' => 'large',
            'density' => 'roomy',
            'imagery' => 'steady and reassuring',
        ],
        '75_plus' => [
            'register' => 'simple, direct vocabulary',
            'pacing' => 'short, unhurried sentences',
            'reassurance' => 'very high',
            'type_size' => 'larger',
            'density' => 'very roomy',
            'imagery' => 'gentle and unhurried',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sex-based emphasis
    |--------------------------------------------------------------------------
    |
    | Sex adjusts the presentation emphasis only. It is never used to select,
    | suggest, or imply anything clinical.
    |
    */

    'sex_emphasis' => [
        'female' => 'warm and personable',
        'male' => 'direct and pragmatic',
        'intersex' => 'neutral and respectful',
        'prefer_not_to_say' => 'neutral and inclusive',
    ],

];
