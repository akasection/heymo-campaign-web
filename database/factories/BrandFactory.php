<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'tone_preset' => 'balanced',
            'flow_preset' => 'balanced',
            'tense_preset' => 'balanced',
            'reading_level_preset' => 'balanced',
            'preferred_terms' => [],
            'avoided_terms' => [],
            'primary_color' => '#2E5BFF',
            'secondary_color' => '#00B8A9',
            'heading_font' => 'public_sans',
            'body_font' => 'public_sans',
            'logo_path' => null,
        ];
    }
}
