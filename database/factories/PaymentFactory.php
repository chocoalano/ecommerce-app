<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string,mixed>
     */
    public function definition(): array
    {
        return [
            'order_id'        => null, // isi di seeder
            'method_id'       => null, // isi sesuai tabel methods di seeder
            'status'          => $this->faker->randomElement(['pending', 'paid', 'failed', 'refunded']),
            'amount'          => $this->faker->randomFloat(2, 10_000, 3_000_000),
            'currency'        => 'IDR',
            'provider_txn_id' => strtoupper('TXN-' . Str::random(10)),
            'metadata_json'   => [
                'provider' => $this->faker->randomElement(['midtrans', 'xendit', 'stripe', 'manual']),
                'method'   => $this->faker->randomElement(['gopay', 'ovo', 'bca_va', 'credit_card', 'bank_transfer']),
                'paid_at'  => $this->faker->optional(0.7)->dateTimeBetween('-30 days', 'now'),
                'notes'    => $this->faker->optional()->sentence(),
            ],
        ];
    }

    /**
     * State helper: pembayaran berhasil.
     */
    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'paid',
        ]);
    }

    /**
     * State helper: pembayaran gagal.
     */
    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => 'failed',
        ]);
    }
}
