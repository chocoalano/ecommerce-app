<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TradeInProgram>
 */
class TradeInProgramFactory extends Factory
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
            'terms_json' => [
                'accepted_brands' => $this->faker->randomElements(['Apple', 'Samsung', 'Xiaomi', 'Asus', 'Acer'], 3),
                'min_condition' => $this->faker->randomElement(['Good', 'Fair', 'Excellent']),
            ],
            'partner_name' => $this->faker->optional()->company(),
        ];
    }
}
