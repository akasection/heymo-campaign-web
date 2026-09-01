<?php

namespace Tests\Unit;

use App\Services\CampaignPromptBuilder;
use Tests\TestCase;

class CampaignPromptBuilderTest extends TestCase
{
    public function test_it_assembles_system_and_user_messages(): void
    {
        $prompt = (new CampaignPromptBuilder)->build($this->input());

        $this->assertSame('campaign-generation-v1', $prompt['version']);
        $this->assertSame('system', $prompt['messages'][0]['role']);
        $this->assertStringContainsString('Campaign generation guardrails', $prompt['messages'][0]['content']);
        $this->assertSame('user', $prompt['messages'][1]['role']);
        $this->assertStringContainsString('Alex', $prompt['messages'][1]['content']);
        $this->assertSame([], $prompt['evidence_ids']);
    }

    public function test_it_omits_offer_and_next_step_from_the_angle(): void
    {
        $input = $this->input();
        $input['angle']['offer'] = 'A truthful discount';
        $input['angle']['next_step'] = 'Order the panel';

        $prompt = (new CampaignPromptBuilder)->build($input);
        $userContent = $prompt['messages'][1]['content'];

        $this->assertStringNotContainsString('Order the panel', $userContent);
        $this->assertStringNotContainsString('truthful discount', $userContent);
    }

    public function test_it_passes_proof_as_the_mechanism_block(): void
    {
        $prompt = (new CampaignPromptBuilder)->build($this->input());

        $this->assertStringContainsString('Approved block: the panel reports the included measurements', $prompt['messages'][1]['content']);
    }

    /**
     * @return array<string, mixed>
     */
    private function input(): array
    {
        return [
            'brand_profile' => ['prompt_skeleton' => 'Write for the brand.'],
            'angle' => [
                'audience' => 'People who feel tired.',
                'trigger_moment' => 'A change in routine.',
                'primary_job' => 'Find a starting point.',
                'tension' => 'Want context without alarm.',
                'desired_outcome' => 'A clearer next step.',
                'single_promise' => 'Get a clearer starting point.',
                'proof' => 'Approved block: the panel reports the included measurements',
                'objection' => 'Worried the results will be confusing.',
                'tone' => 'reassuring',
            ],
            'visitor' => ['preferred_name' => 'Alex'],
            'intent' => [
                'sub_interest' => 'daily_energy',
                'trigger' => 'recent_change',
                'concern' => 'I can get through the day, but I do not feel like myself.',
            ],
            'presentation_profile' => ['age_group' => '18_29'],
        ];
    }
}
