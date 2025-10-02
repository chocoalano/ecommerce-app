<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PromotionGift>
 */
class PromotionGiftFactory extends Factory
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
            'gift_variant_id' => null,
            'min_spend' => $this->faker->randomFloat(2, 50000, 300000),
            'min_qty' => $this->faker->numberBetween(0, 3),
        ];
    }
}
