<?php

namespace Database\Factories\OrderProduct;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderProduct\PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code'      => $this->faker->unique()->randomElement(['CC','VA','QRIS','E-WALLET','COD']),
            'name'      => $this->faker->randomElement(['Credit Card','Virtual Account','QRIS','E-Wallet','COD']),
            'is_active' => true,
        ];
    }

}
