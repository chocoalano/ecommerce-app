<?php

namespace Database\Factories\OrderProduct;

use App\Models\OrderProduct\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderProduct\PaymentTransaction>
 */
class PaymentTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'status'     => Payment::ST_INITIATED,
            'amount'     => 0,
            'raw_json'   => null,
            'created_at' => now(),
        ];
    }

    public function forPayment(Payment $p, string $status, float $amount): self
    {
        return $this->state(fn () => [
            'payment_id' => $p->id,
            'status'     => $status,
            'amount'     => $amount,
            'created_at' => now(),
        ]);
    }

}
