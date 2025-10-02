<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = strtoupper($this->faker->bothify('VC-##??##'));
        $start = $this->faker->optional(0.8)->dateTimeBetween('-10 days', '+2 days');
        $end = $this->faker->optional(0.8)->dateTimeBetween('+3 days', '+30 days');


        return [
            'promotion_id' => null,
            'code' => $code,
            'is_stackable' => $this->faker->boolean(30),
            'start_at' => $start,
            'end_at' => $end,
            'max_redemption' => $this->faker->optional()->numberBetween(50, 10000),
            'per_user_limit' => $this->faker->optional()->numberBetween(1, 5),
            'conditions_json' => [
                'channel' => $this->faker->randomElement(['web', 'mobile', 'pos']),
            ],
        ];
    }
}
