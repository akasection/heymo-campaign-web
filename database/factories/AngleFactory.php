<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Angle>
 */
class AngleFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->sentence(3);

        return [
            'brand_id' => Brand::factory(),
            'landing_identifier' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'audience' => fake()->paragraph(),
            'trigger_moment' => fake()->paragraph(),
            'primary_job' => fake()->paragraph(),
            'tension' => fake()->paragraph(),
            'desired_outcome' => fake()->paragraph(),
            'single_promise' => fake()->sentence(),
            'proof' => 'Approved evidence block: '.fake()->sentence(),
            'objection' => fake()->sentence(),
            'offer' => 'Order at the stated price with no hidden conditions.',
            'tone' => fake()->randomElement(array_keys(config('angles.tones', []))),
            'next_step' => 'Review the panel details and choose whether to order.',
        ];
    }
}
