<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductMedia>
 */
class ProductMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'url' => asset('storage/images/galaxy-z-flip7-share-image.png'),
            'type' => $this->faker->randomElement(['image', 'video']),
            'alt_text' => $this->faker->sentence(3),
            'sort_order' => $this->faker->numberBetween(1, 10),
            'is_primary' => $this->faker->boolean(),
            'created_at' => now(),
        ];
    }
}
