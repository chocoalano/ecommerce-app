<?php

namespace Database\Factories\OrderProduct;

use App\Models\OrderProduct\Order;
use App\Models\OrderProduct\OrderReturn;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderProduct\OrderReturn>
 */
class OrderReturnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id'     => Order::factory(),
            'status'       => OrderReturn::ST_REQUESTED,
            'reason'       => $this->faker->randomElement(['Size not fit','Damaged box','Wrong item']),
            'requested_at' => now(),
            'processed_at' => null,
        ];
    }

    public function approved(): self
    {
        return $this->state(fn () => ['status' => OrderReturn::ST_APPROVED, 'processed_at' => now()]);
    }

}
