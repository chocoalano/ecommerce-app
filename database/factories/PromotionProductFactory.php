<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PromotionProduct>
 */
class PromotionProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'promotion_id' => null,
            'product_id' => null,
            'variant_id' => null,
            'min_qty' => $this->faker->numberBetween(1, 5),
            'discount_value' => $this->faker->optional(0.5)->randomFloat(2, 1000, 100000),
            'discount_percent' => $this->faker->optional(0.5)->randomFloat(2, 1, 70),
            'bundle_price' => $this->faker->optional(0.3)->randomFloat(2, 10000, 999999),
        ];
    }
}
