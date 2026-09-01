<?php

namespace Tests\Unit;

use App\Services\PresentationProfileResolver;
use Tests\TestCase;

class PresentationProfileResolverTest extends TestCase
{
    public function test_it_resolves_an_age_group_profile_with_sex_emphasis(): void
    {
        $profile = (new PresentationProfileResolver)->resolve('18_29', 'female');

        $this->assertSame('18_29', $profile['age_group']);
        $this->assertSame('low', $profile['reassurance']);
        $this->assertSame('warm and personable', $profile['sex_emphasis']);
    }

    public function test_different_age_groups_produce_different_profiles(): void
    {
        $young = (new PresentationProfileResolver)->resolve('18_29', 'female');
        $older = (new PresentationProfileResolver)->resolve('60_74', 'female');

        $this->assertNotSame($young['reassurance'], $older['reassurance']);
        $this->assertNotSame($young['density'], $older['density']);
        $this->assertNotSame($young['type_size'], $older['type_size']);
    }

    public function test_unknown_age_group_throws(): void
    {
        $this->expectException(\RuntimeException::class);

        (new PresentationProfileResolver)->resolve('unknown', 'female');
    }

    public function test_unknown_sex_falls_back_to_neutral_emphasis(): void
    {
        $profile = (new PresentationProfileResolver)->resolve('18_29', 'other');

        $this->assertSame('neutral and inclusive', $profile['sex_emphasis']);
    }
}
