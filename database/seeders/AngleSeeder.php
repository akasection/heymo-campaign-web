<?php

namespace Database\Seeders;

use App\Models\Angle;
use App\Models\Brand;
use Illuminate\Database\Seeder;

class AngleSeeder extends Seeder
{
    public function run(): void
    {
        $brands = Brand::query()
            ->whereIn('slug', ['lexical-labs', 'xo-health-group'])
            ->get()
            ->keyBy('slug');

        foreach ($this->definitions() as $brandSlug => $angles) {
            $brand = $brands->get($brandSlug);

            if (! $brand instanceof Brand) {
                continue;
            }

            foreach ($angles as $attributes) {
                $angle = Angle::withTrashed()->firstOrNew([
                    'brand_id' => $brand->id,
                    'slug' => $attributes['slug'],
                ]);

                $angle->fill($attributes);
                $angle->save();

                if ($angle->trashed()) {
                    $angle->restore();
                }
            }
        }
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    private function definitions(): array
    {
        return [
            'lexical-labs' => [
                [
                    'name' => 'Find the thread in your tired days',
                    'slug' => 'fatigue',
                    'landing_identifier' => 'fatigue',
                    'audience' => 'People who are tired of treating persistent low energy as something they simply have to push through.',
                    'trigger_moment' => 'A familiar routine has started feeling harder, or rest no longer feels like a reliable reset.',
                    'primary_job' => 'Turn a vague change in energy into a clearer starting point for a personal conversation.',
                    'tension' => 'They want useful context without being alarmed or told that one symptom explains everything.',
                    'desired_outcome' => 'A steadier sense of what to pay attention to next.',
                    'single_promise' => 'Get a clearer starting point for understanding a change in your energy.',
                    'proof' => 'Approved block: the panel reports the included measurements in a plain-language results view.',
                    'objection' => 'I am worried the results will be confusing or make me more anxious.',
                    'offer' => 'The panel price and included measurements are shown before checkout with no hidden conditions.',
                    'tone' => 'reassuring',
                    'next_step' => 'Review the fatigue panel details and decide whether ordering feels right for you.',
                    'next_step_url' => 'https://example.com/order',
                ],
                [
                    'name' => 'Make progress feel more workable',
                    'slug' => 'obesity',
                    'landing_identifier' => 'obesity',
                    'audience' => 'People working toward a personal weight or wellness goal who want context instead of another extreme plan.',
                    'trigger_moment' => 'A routine change or stalled effort has made guessing feel less useful than starting with a baseline.',
                    'primary_job' => 'Replace all-or-nothing thinking with a more informed, manageable next conversation.',
                    'tension' => 'They want to feel respected and supported, not reduced to a number or promised a quick fix.',
                    'desired_outcome' => 'A practical picture of where they are starting and what they want to discuss next.',
                    'single_promise' => 'Start your next wellness step with context you can actually use.',
                    'proof' => 'Approved block: the results view gives you the measurements included in this panel and their reported values.',
                    'objection' => 'I have tried before and do not want another experience that leaves me feeling judged.',
                    'offer' => 'The panel price, included measurements, and checkout terms are visible before you decide.',
                    'tone' => 'warm',
                    'next_step' => 'Read what the panel includes and choose whether it fits the kind of context you want.',
                    'next_step_url' => 'https://example.com/order',
                ],
                [
                    'name' => 'Build a better before-the-wedding conversation',
                    'slug' => 'premarital-check',
                    'landing_identifier' => 'premarital-check',
                    'audience' => 'Engaged couples who want to make health part of planning their life together in a thoughtful way.',
                    'trigger_moment' => 'An engagement or shared planning milestone has opened the door to a more honest wellness conversation.',
                    'primary_job' => 'Make a private health check feel like a shared, practical act of care.',
                    'tension' => 'They want openness without turning an exciting season into a frightening checklist.',
                    'desired_outcome' => 'A calmer, more informed conversation they can continue together.',
                    'single_promise' => 'Make room for a thoughtful health conversation before your next chapter begins.',
                    'proof' => 'Approved block: each person receives a private results view for the measurements included in their own panel.',
                    'objection' => 'I do not want this to create worry between us when we are planning something joyful.',
                    'offer' => 'Each panel is priced and described separately before checkout, with no hidden conditions.',
                    'tone' => 'warm',
                    'next_step' => 'Explore the pre-marital panel details together and decide whether to order.',
                    'next_step_url' => 'https://example.com/order',
                ],
                [
                    'name' => 'Train from a clearer baseline',
                    'slug' => 'athletic-performance',
                    'landing_identifier' => 'athletic-performance',
                    'audience' => 'Active people who want more context for training and recovery without turning every plateau into a problem.',
                    'trigger_moment' => 'A new program, event, plateau, or return to training has made their baseline feel worth revisiting.',
                    'primary_job' => 'Give training decisions a cleaner starting point before changing the whole plan.',
                    'tension' => 'They want useful numbers, not hype or a promise that a panel will replace coaching and consistency.',
                    'desired_outcome' => 'A grounded set of information to bring into the next training conversation.',
                    'single_promise' => 'Bring more context to the way you train and recover.',
                    'proof' => 'Approved block: the panel lists the included measurements and returns the reported values in one results view.',
                    'objection' => 'I already track a lot and do not want another set of numbers without a use for them.',
                    'offer' => 'The panel contents and price are shown clearly before checkout with no hidden conditions.',
                    'tone' => 'matter_of_fact',
                    'next_step' => 'Review the performance panel contents and decide whether it supports your next block.',
                    'next_step_url' => 'https://example.com/order',
                ],
            ],
            'xo-health-group' => [
                [
                    'name' => 'Measured context for persistent fatigue',
                    'slug' => 'fatigue',
                    'landing_identifier' => 'fatigue',
                    'audience' => 'Adults seeking a measured starting point for a persistent change in energy, with clear limits on what a panel can tell them.',
                    'trigger_moment' => 'A sustained change in daily energy has prompted them to gather information before making a further decision.',
                    'primary_job' => 'Organize a useful baseline for a calm, informed discussion with an appropriate professional.',
                    'tension' => 'They require clarity and restraint; speculative explanations would undermine trust.',
                    'desired_outcome' => 'A documented starting point and a better-framed next conversation.',
                    'single_promise' => 'Begin with measured information about the panel measurements you choose to review.',
                    'proof' => 'Approved block: the laboratory-reviewed report identifies the measurements included in the selected panel.',
                    'objection' => 'I am concerned that a consumer test will overstate what its findings mean.',
                    'offer' => 'Panel contents, price, and checkout terms are stated before purchase and contain no hidden conditions.',
                    'tone' => 'authoritative',
                    'next_step' => 'Review the panel scope and decide whether it is an appropriate information-gathering step.',
                    'next_step_url' => 'https://example.com/order',
                ],
                [
                    'name' => 'Context for a sustainable wellness plan',
                    'slug' => 'obesity',
                    'landing_identifier' => 'obesity',
                    'audience' => 'Adults working toward a personal wellness goal who want measured context without judgment or unsupported promises.',
                    'trigger_moment' => 'A change in routine or an unresolved goal has made a documented baseline feel useful.',
                    'primary_job' => 'Support a more informed wellness discussion while keeping the limits of the data clear.',
                    'tension' => 'They need respect and precision, not a simplified story about body size or a guaranteed result.',
                    'desired_outcome' => 'A measured starting point they can consider alongside their broader context.',
                    'single_promise' => 'Add measured context to the wellness conversation you are already having.',
                    'proof' => 'Approved block: the report states the measurements included in the selected panel and presents the reported values.',
                    'objection' => 'I do not want a test to imply that one set of measurements defines my health.',
                    'offer' => 'The panel scope, price, and checkout terms are available for review before purchase.',
                    'tone' => 'authoritative',
                    'next_step' => 'Review the panel scope and decide whether it belongs in your broader wellness plan.',
                    'next_step_url' => 'https://example.com/order',
                ],
                [
                    'name' => 'A considered baseline for two',
                    'slug' => 'premarital-check',
                    'landing_identifier' => 'premarital-check',
                    'audience' => 'Couples who want a private, considered way to include health information in planning their shared future.',
                    'trigger_moment' => 'A significant relationship milestone has made a calm, practical wellness conversation timely.',
                    'primary_job' => 'Provide a structured starting point without creating unnecessary alarm.',
                    'tension' => 'They want medical seriousness and privacy while protecting the meaning of the occasion.',
                    'desired_outcome' => 'A shared conversation grounded in each person\'s own reported information.',
                    'single_promise' => 'Create a measured starting point for a health conversation you can have together.',
                    'proof' => 'Approved block: each individual report identifies the measurements included in that person’s selected panel.',
                    'objection' => 'I am not sure a health check can be both serious and supportive for a couple.',
                    'offer' => 'Each panel\'s contents, price, and purchase terms are stated separately before checkout.',
                    'tone' => 'reassuring',
                    'next_step' => 'Review the two panel options and decide whether this is the right time to order.',
                    'next_step_url' => 'https://example.com/order',
                ],
                [
                    'name' => 'Evidence for the next training conversation',
                    'slug' => 'athletic-performance',
                    'landing_identifier' => 'athletic-performance',
                    'audience' => 'Athletes and active adults who want a measured baseline to bring into training and recovery decisions.',
                    'trigger_moment' => 'A program change, plateau, event, or return to training has made additional context relevant.',
                    'primary_job' => 'Place selected measurements alongside training observations without replacing qualified coaching.',
                    'tension' => 'They expect precision and practical relevance, not performance guarantees or sensational claims.',
                    'desired_outcome' => 'A clear report to consider with the rest of their training information.',
                    'single_promise' => 'Bring measured context to your next training and recovery discussion.',
                    'proof' => 'Approved block: the report identifies the measurements included in the selected panel and presents the reported values.',
                    'objection' => 'I do not want more data unless the scope and limitations are clear.',
                    'offer' => 'The selected panel\'s scope, price, and checkout terms are visible before purchase.',
                    'tone' => 'matter_of_fact',
                    'next_step' => 'Review the panel scope and decide whether it is useful for your next training discussion.',
                    'next_step_url' => 'https://example.com/order',
                ],
            ],
        ];
    }
}
