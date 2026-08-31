<?php

return [
    'fatigue' => [
        'view' => 'LandingPages.fatigue',
        'title' => 'A clearer way to look at low energy',
        'eyebrow' => 'For days that feel harder than they should',
        'description' => 'A short, focused starting point for people who want useful context around persistent tiredness without turning one symptom into a diagnosis.',
        'quiz' => [
            'title' => 'Let us meet the moment you are in',
            'description' => 'A few answers help us keep the next step relevant to your kind of tiredness.',
            'sub_interest' => [
                'label' => 'What would you most like to understand?',
                'options' => [
                    ['value' => 'daily_energy', 'label' => 'Why my everyday energy feels different'],
                    ['value' => 'sleep_recovery', 'label' => 'Why rest is not feeling restorative'],
                    ['value' => 'focus', 'label' => 'How low energy is affecting my focus'],
                    ['value' => 'stamina', 'label' => 'How to feel steadier through the day'],
                ],
            ],
            'trigger' => [
                'label' => 'What brought you here today?',
                'options' => [
                    ['value' => 'recent_change', 'label' => 'I noticed a recent change'],
                    ['value' => 'busy_stretch', 'label' => 'A busy stretch has gone on too long'],
                    ['value' => 'sleep_not_restoring', 'label' => 'Sleep is not giving me my usual reset'],
                    ['value' => 'want_baseline', 'label' => 'I want a clearer baseline'],
                ],
            ],
            'concern_label' => 'What feels most unresolved about your energy?',
            'concern_placeholder' => 'For example: I can get through the day, but I do not feel like myself.',
        ],
    ],
    'obesity' => [
        'view' => 'LandingPages.obesity',
        'title' => 'Progress that starts with useful context',
        'eyebrow' => 'For people tired of guessing at their next step',
        'description' => 'A respectful, numbers-first starting point for understanding your current wellness context and choosing a next step that feels workable.',
        'quiz' => [
            'title' => 'Tell us what progress means to you',
            'description' => 'Your answers keep the experience focused on your goals, not assumptions about your body.',
            'sub_interest' => [
                'label' => 'Where would more context help most?',
                'options' => [
                    ['value' => 'understand_trends', 'label' => 'Understanding patterns over time'],
                    ['value' => 'steady_habits', 'label' => 'Building steadier everyday habits'],
                    ['value' => 'energy_movement', 'label' => 'Having more energy for movement'],
                    ['value' => 'confidence', 'label' => 'Feeling more confident in my next step'],
                ],
            ],
            'trigger' => [
                'label' => 'What made now feel like a good time to look?',
                'options' => [
                    ['value' => 'routine_shift', 'label' => 'My routine has changed'],
                    ['value' => 'health_goal', 'label' => 'I am working toward a personal goal'],
                    ['value' => 'frustration', 'label' => 'I am tired of trying without context'],
                    ['value' => 'starting_fresh', 'label' => 'I want to begin with a clear baseline'],
                ],
            ],
            'concern_label' => 'What would make this feel more useful and less overwhelming?',
            'concern_placeholder' => 'For example: I want information I can understand and use at my own pace.',
        ],
    ],
    'premarital-check' => [
        'view' => 'LandingPages.premarital-check',
        'title' => 'A thoughtful health conversation for two',
        'eyebrow' => 'For couples planning the life ahead',
        'description' => 'A private, practical way to make health part of the conversation before the ceremony, without turning it into a source of fear.',
        'quiz' => [
            'title' => 'What would make this conversation easier?',
            'description' => 'Tell us what you want to bring into the conversation so the next step feels personal and considered.',
            'sub_interest' => [
                'label' => 'What matters most to you as a couple?',
                'options' => [
                    ['value' => 'shared_baseline', 'label' => 'Starting with a shared baseline'],
                    ['value' => 'family_planning', 'label' => 'Planning thoughtfully for family life'],
                    ['value' => 'wellness_conversation', 'label' => 'Making wellness easier to discuss'],
                    ['value' => 'peace_of_mind', 'label' => 'Entering the next chapter with peace of mind'],
                ],
            ],
            'trigger' => [
                'label' => 'What brought this onto your list now?',
                'options' => [
                    ['value' => 'engagement', 'label' => 'We recently got engaged'],
                    ['value' => 'upcoming_wedding', 'label' => 'Our wedding is getting close'],
                    ['value' => 'planning', 'label' => 'We are planning the life after the wedding'],
                    ['value' => 'curiosity', 'label' => 'We want to start an open conversation'],
                ],
            ],
            'concern_label' => 'What would help this feel supportive for both of you?',
            'concern_placeholder' => 'For example: I want it to feel like a shared step, not a scary checklist.',
        ],
    ],
    'athletic-performance' => [
        'view' => 'LandingPages.athletic-performance',
        'title' => 'Train with more context, not more guesswork',
        'eyebrow' => 'For athletes who want to understand their baseline',
        'description' => 'A focused look at the information that can help you have a better conversation with your training, recovery, and next block.',
        'quiz' => [
            'title' => 'What are you training toward?',
            'description' => 'A few specifics help us keep the experience relevant to the way you actually train.',
            'sub_interest' => [
                'label' => 'Which part of your training has your attention?',
                'options' => [
                    ['value' => 'training_recovery', 'label' => 'Recovering well between sessions'],
                    ['value' => 'endurance', 'label' => 'Building endurance with intention'],
                    ['value' => 'strength', 'label' => 'Supporting strength work'],
                    ['value' => 'baseline', 'label' => 'Establishing a useful baseline'],
                ],
            ],
            'trigger' => [
                'label' => 'What changed before you started looking?',
                'options' => [
                    ['value' => 'new_program', 'label' => 'I started a new program'],
                    ['value' => 'plateau', 'label' => 'My progress has plateaued'],
                    ['value' => 'event', 'label' => 'I am preparing for an event'],
                    ['value' => 'returning', 'label' => 'I am returning after time away'],
                ],
            ],
            'concern_label' => 'What would make the information actionable for your training?',
            'concern_placeholder' => 'For example: I want to know what to pay attention to before changing my whole plan.',
        ],
    ],
];
