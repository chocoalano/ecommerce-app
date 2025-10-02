<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string,mixed>
     */
    public function definition(): array
    {
        return [
            'order_no'            => strtoupper($this->faker->bothify('ORD-########')),
            'user_id'             => null, // set di seeder
            'currency'            => 'IDR',
            'status'              => $this->faker->randomElement([
                                        'PENDING','PAID','PROCESSING','SHIPPED','COMPLETED','CANCELED','REFUNDED','PARTIAL_REFUND'
                                    ]),
            'subtotal_amount'     => 0, // dihitung ulang saat isi items
            'discount_amount'     => 0,
            'shipping_amount'     => $this->faker->randomFloat(2, 0, 30000),
            'tax_amount'          => $this->faker->randomFloat(2, 0, 20000),
            'grand_total'         => 0,
            'shipping_address_id' => null,
            'billing_address_id'  => null,
            'applied_promos'      => [], // cast: array
            'notes'               => $this->faker->optional(0.3)->sentence(),
            'placed_at'           => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * State: order sudah dibayar.
     */
    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'paid',
        ]);
    }

    /**
     * State: order dibatalkan.
     */
    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => 'cancelled',
        ]);
    }
}
