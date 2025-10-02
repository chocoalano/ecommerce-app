<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string,mixed>
     */
    public function definition(): array
    {
        $qty        = $this->faker->numberBetween(1, 5);
        $unitPrice  = $this->faker->randomFloat(2, 10_000, 200_000);
        $lineTotal  = $unitPrice * $qty;
        // batasi diskon maksimal 40% dari line total agar realistis
        $discount   = $this->faker->randomFloat(2, 0, $lineTotal * 0.4);
        $rowTotal   = max(0, $lineTotal - $discount);

        return [
            'order_id'        => null, // isi di seeder
            'product_id'      => null, // isi di seeder
            'variant_id'      => null, // isi di seeder
            'name'            => $this->faker->words(3, true),
            'sku'             => strtoupper($this->faker->bothify('SKU-########')),
            'qty'             => $qty,
            'unit_price'      => $unitPrice,
            'discount_amount' => $discount,
            'row_total'       => $rowTotal,
            'meta_json'       => [
                'tax_rate'   => $this->faker->randomElement([0, 0.1, 0.11]), // contoh PPN
                'notes'      => $this->faker->optional(0.2)->sentence(),
                'attributes' => $this->faker->randomElement([
                    ['size' => 'S', 'color' => 'Green'],
                    ['size' => 'M', 'color' => 'Black'],
                    ['size' => 'L', 'color' => 'White'],
                    null
                ]),
            ],
        ];
    }

    /**
     * State helper: pakai data dari variant (name, sku, unit_price).
     * Panggil seperti:
     * OrderItem::factory()->forProductVariant($variant)->create(['order_id' => $order->id]);
     */
    public function forProductVariant($variant): static
    {
        return $this->state(function () use ($variant) {
            $qty       = $this->faker->numberBetween(1, 5);
            $unitPrice = method_exists($variant, 'getAttribute') ? ($variant->base_price ?? 0) : 0;
            $unitPrice = $unitPrice ?: $this->faker->randomFloat(2, 10_000, 200_000);

            $lineTotal = $unitPrice * $qty;
            $discount  = $this->faker->randomFloat(2, 0, $lineTotal * 0.3);
            $rowTotal  = max(0, $lineTotal - $discount);

            return [
                'product_id'      => $variant->product_id ?? null,
                'variant_id'      => $variant->id ?? null,
                'name'            => $variant->name ?? $this->faker->words(3, true),
                'sku'             => $variant->variant_sku ?? strtoupper($this->faker->bothify('SKU-########')),
                'qty'             => $qty,
                'unit_price'      => $unitPrice,
                'discount_amount' => $discount,
                'row_total'       => $rowTotal,
            ];
        });
    }
}
