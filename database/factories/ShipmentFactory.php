<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shipment>
 */
class ShipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => null,
            'carrier' => $this->faker->randomElement(['JNE', 'J&T', 'SICEPAT', 'ANTERAJA', 'POSID']),
            'service' => $this->faker->randomElement(['REG', 'YES', 'BEST', 'ECO']),
            'tracking_number' => strtoupper($this->faker->bothify('TRK#########')),
            'status' => $this->faker->randomElement(['pending', 'packed', 'shipped', 'delivered', 'returned']),
            'shipped_at' => $this->faker->optional(0.6)->dateTimeBetween('-20 days', 'now'),
            'delivered_at' => $this->faker->optional(0.5)->dateTimeBetween('-10 days', 'now'),
        ];
    }
}
