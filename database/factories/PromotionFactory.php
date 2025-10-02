<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            'PERCENT_DISCOUNT',
            'FIXED_DISCOUNT',
            'CASHBACK',
            'BUNDLE_PRICE',
            'GIFT_WITH_PURCHASE',
            'BANK_INSTALLMENT',
            'PAYMENT_METHOD_DISCOUNT',
            'FLASH_SALE',
            'TRADE_IN'
        ];


        $start = $this->faker->dateTimeBetween('-15 days', '+1 days');
        $end = (clone $start)->modify('+' . rand(3, 20) . ' days');


        $code = strtoupper($this->faker->bothify('PRM-###??'));
        $name = $this->faker->words(3, true);


        return [
            'code' => $code,
            'name' => ucwords($name),
            'type' => $this->faker->randomElement($types),
            'landing_slug' => str($name)->slug(),
            'description' => $this->faker->paragraph(),
            'start_at' => $start,
            'end_at' => $end,
            'is_active' => $this->faker->boolean(80),
            'priority' => $this->faker->numberBetween(1, 200),
            'max_redemption' => $this->faker->optional()->numberBetween(100, 10000),
            'per_user_limit' => $this->faker->optional()->numberBetween(1, 10),
            'conditions_json' => [
                'min_spend' => $this->faker->optional()->numberBetween(50000, 300000),
                'channels' => $this->faker->randomElements(['web', 'pos', 'mobile'], rand(1, 3)),
            ],
        ];
    }
}
