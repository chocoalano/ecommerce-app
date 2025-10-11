<?php

namespace Database\Factories\OrderProduct;

use App\Models\OrderProduct\Order;
use App\Models\OrderProduct\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderProduct\Refund>
 */
class RefundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id'  => Order::factory(),
            'payment_id'=> null, // bisa diisi via state forPayment()
            'status'    => Payment::ST_REFUNDED,
            'amount'    => 0,
            'reason'    => $this->faker->optional()->sentence(4),
        ];
    }

    public function forOrder(Order $o, float $amount, ?Payment $p = null): self
    {
        return $this->state(fn () => [
            'order_id'  => $o->id,
            'payment_id'=> $p?->id,
            'amount'    => $amount,
        ]);
    }

}
