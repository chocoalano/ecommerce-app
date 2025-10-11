<?php

namespace Database\Factories\OrderProduct;

use App\Models\OrderProduct\Courier;
use App\Models\OrderProduct\Order;
use App\Models\OrderProduct\Shipment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderProduct\Shipment>
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
            'order_id'     => Order::factory(),
            'courier_id'   => Courier::factory(),
            'tracking_no'  => strtoupper(Str::random(12)),
            'status'       => Shipment::ST_READY,
            'shipped_at'   => null,
            'delivered_at' => null,
            'shipping_fee' => $this->faker->randomFloat(2, 0, 50000),
        ];
    }

    public function shipped(): self
    {
        return $this->state(fn () => [
            'status' => Shipment::ST_TRANSIT,
            'shipped_at' => now(),
        ]);
    }

    public function delivered(): self
    {
        return $this->state(fn () => [
            'status' => Shipment::ST_DELIVERED,
            'shipped_at' => now()->subDays(2),
            'delivered_at' => now(),
        ]);
    }

}
