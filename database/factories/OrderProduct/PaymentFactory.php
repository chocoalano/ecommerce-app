<?php

namespace Database\Factories\OrderProduct;

use App\Models\OrderProduct\Order;
use App\Models\OrderProduct\Payment;
use App\Models\OrderProduct\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderProduct\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $method = PaymentMethod::factory()->create();

        return [
            'order_id'       => Order::factory(),
            'method_id'      => $method->id,
            'status'         => Payment::ST_INITIATED,
            'amount'         => 0,
            'currency'       => 'IDR',
            'provider_txn_id'=> null,
            'metadata_json'  => null,
        ];
    }

    public function forOrder(Order $order, ?float $amount = null): self
    {
        return $this->state(function () use ($order, $amount) {
            return ['order_id' => $order->id, 'amount' => $amount ?? (float)$order->grand_total];
        });
    }

    public function captured(): self   { return $this->state(fn () => ['status' => Payment::ST_CAPTURED]); }
    public function failed(): self     { return $this->state(fn () => ['status' => Payment::ST_FAILED]); }
    public function refunded(): self   { return $this->state(fn () => ['status' => Payment::ST_REFUNDED]); }

}
