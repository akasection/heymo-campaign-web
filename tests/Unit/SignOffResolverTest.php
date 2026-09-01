<?php

namespace Tests\Unit;

use App\Services\SignOffResolver;
use Tests\TestCase;

class SignOffResolverTest extends TestCase
{
    public function test_it_maps_tone_and_tense_to_a_valediction(): void
    {
        $resolver = new SignOffResolver;

        $this->assertSame('Sincerely', $resolver->resolve('formal', 'serious', 'XO Health Group')['valediction']);
        $this->assertSame('Warm regards', $resolver->resolve('balanced', 'relaxed', 'Lexical Labs')['valediction']);
        $this->assertSame('Take care', $resolver->resolve('informal', 'relaxed', 'Lexical Labs')['valediction']);
        $this->assertSame('Thanks', $resolver->resolve('informal', 'serious', 'Lexical Labs')['valediction']);
    }

    public function test_it_derives_the_signature_identity_from_the_brand_name(): void
    {
        $resolver = new SignOffResolver;

        $this->assertSame('The Lexical Labs team', $resolver->resolve('informal', 'relaxed', 'Lexical Labs')['signature']);
    }

    public function test_unknown_presets_fall_back_to_balanced_balanced(): void
    {
        $resolver = new SignOffResolver;

        $result = $resolver->resolve('unknown', 'unknown', '');

        $this->assertSame('Best regards', $result['valediction']);
        $this->assertSame('The team', $result['signature']);
    }
}
