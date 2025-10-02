<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BankInstallment>
 */
class BankInstallmentFactory extends Factory
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
            'bank_code' => $this->faker->randomElement(['BCA', 'BNI', 'BRI', 'MANDIRI', 'CIMB']),
            'tenor_months' => $this->faker->randomElement([3, 6, 12]),
            'interest_rate_pa' => $this->faker->optional()->randomFloat(4, 0.0, 0.24),
            'admin_fee' => $this->faker->randomFloat(2, 0, 50000),
            'min_spend' => $this->faker->randomFloat(2, 500000, 5000000),
        ];
    }
}
