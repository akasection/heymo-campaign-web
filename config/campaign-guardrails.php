<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Deterministic campaign guardrails
    |--------------------------------------------------------------------------
    |
    | Global prohibited-language patterns enforced by the deterministic policy
    | validator. Brand-specific avoided terms are supplied per campaign from
    | the owning Brand record. The validator is code, never a second model.
    |
    */

    'prohibited' => [

        'clinical_claims' => [
            ['pattern' => '/\b(?:diagnos\w*|cure[sd]?|heal(?:s|ing|ed)?|treat(?:s|ment|ing|ed)?)\b/i', 'message' => 'unsupported diagnosis, cure, or treatment language'],
            ['pattern' => '/\b(?:will (?:fix|end|stop|eliminate)|guarantee\w*)\b/i', 'message' => 'promised outcome or guarantee language'],
            ['pattern' => '/\b(?:your results (?:will|are likely to)|you (?:likely|probably) have)\b/i', 'message' => 'predicted or inferred result language'],
        ],

        'false_urgency' => [
            ['pattern' => '/\b(?:limited time|only today|act now|last chance|ends tonight|while supplies last)\b/i', 'message' => 'false urgency language'],
            ['pattern' => '/\b(?:limited|exclusive)\s+(?:spots?|availability|access|supply)\b/i', 'message' => 'scarcity language'],
            ['pattern' => '/\b(?:countdown|expires? soon|hurry)\b/i', 'message' => 'urgency language'],
        ],

        'misleading_comparison' => [
            ['pattern' => '/\b(?:better than|compare(?:d)? to|number one|#1|best in class)\b/i', 'message' => 'misleading comparison language'],
            ['pattern' => '/\b(?:hidden|secret)\s+(?:terms?|conditions?|fees?)\b/i', 'message' => 'hidden-condition language'],
        ],

    ],

];
