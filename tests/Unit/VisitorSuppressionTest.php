<?php

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Suppression;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorSuppressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_suppression_blocks_consent_even_when_a_consent_record_exists(): void
    {
        $visitor = $this->makeVisitor('remy@example.test');
        $visitor->consentRecords()->create([
            'channel' => 'email',
            'email' => $visitor->email,
            'source' => 'test',
            'policy_version' => 'email-consent-v1',
            'consented_at' => now(),
        ]);

        $this->assertTrue($visitor->hasActiveEmailConsent());

        $visitor->suppressions()->create([
            'channel' => Suppression::CHANNEL_EMAIL,
            'reason' => Suppression::REASON_UNSUBSCRIBE,
            'source' => 'unsubscribe:link',
            'suppressed_at' => now(),
        ]);

        $this->assertTrue($visitor->isSuppressed());
        $this->assertFalse($visitor->hasActiveEmailConsent());
    }

    public function test_visitor_without_consent_is_not_active(): void
    {
        $visitor = $this->makeVisitor('no-consent@example.test');

        $this->assertFalse($visitor->hasActiveEmailConsent());
        $this->assertFalse($visitor->isSuppressed());
    }

    private function makeVisitor(string $email): Visitor
    {
        $brand = Brand::factory()->create();

        return Visitor::query()->create([
            'brand_id' => $brand->id,
            'preferred_name' => 'Remy',
            'email' => $email,
        ]);
    }
}
