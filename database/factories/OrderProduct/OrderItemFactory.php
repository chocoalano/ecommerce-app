<?php

namespace Database\Factories\OrderProduct;

use App\Models\OrderProduct\Order;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderProduct\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory()->create();
        $qty   = $this->faker->numberBetween(1, 3);
        $price = (float)$product->base_price;
        $disc  = $this->faker->randomElement([0, 0, 0, 5000]); // kadang ada diskon baris

        return [
            'order_id'        => Order::factory(),
            'product_id'      => $product->id,
            'name'            => $product->name,
            'sku'             => $product->sku,
            'qty'             => $qty,
            'unit_price'      => $price,
            'discount_amount' => $disc,
            'row_total'       => $qty * $price - $disc,
            'weight_gram'     => $product->weight_gram,
            'length_mm'       => $product->length_mm,
            'width_mm'        => $product->width_mm,
            'height_mm'       => $product->height_mm,
            'meta_json'       => null,
        ];
    }

    public function forOrder(Order $order): self
    {
        return $this->state(fn () => ['order_id' => $order->id]);
    }

    public function forProduct(Product $product, int $qty = 1, ?float $unitPrice = null, int $disc = 0): self
    {
        return $this->state(function () use ($product, $qty, $unitPrice, $disc) {
            $price = $unitPrice ?? (float)$product->base_price;
            return [
                'product_id'      => $product->id,
                'name'            => $product->name,
                'sku'             => $product->sku,
                'qty'             => $qty,
                'unit_price'      => $price,
                'discount_amount' => $disc,
                'row_total'       => $qty * $price - $disc,
                'weight_gram'     => $product->weight_gram,
                'length_mm'       => $product->length_mm,
                'width_mm'        => $product->width_mm,
                'height_mm'       => $product->height_mm,
            ];
        });
    }

}
