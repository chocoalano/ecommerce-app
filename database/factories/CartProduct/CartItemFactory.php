<?php

namespace Database\Factories\CartProduct;

use App\Models\CartProduct\Cart;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartProduct\CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // default: harga ambil dari product; kalau belum ada, fallback ke faker
        $product = Product::factory()->create();
        $qty     = $this->faker->numberBetween(1, 3);
        $price   = (float)$product->base_price;

        return [
            'cart_id'      => Cart::factory(),
            'product_id'   => $product->id,
            'qty'          => $qty,
            'unit_price'   => $price,
            'currency'     => 'IDR',
            'product_sku'  => $product->sku,
            'product_name' => $product->name,
            'row_total'    => $qty * $price,
            'meta_json'    => null,
        ];
    }

    public function forCart(Cart $cart): self
    {
        return $this->state(fn () => ['cart_id' => $cart->id, 'currency' => $cart->currency ?? 'IDR']);
    }

    public function forProduct(Product $product, ?float $unitPrice = null): self
    {
        return $this->state(function () use ($product, $unitPrice) {
            $price = $unitPrice ?? (float)$product->base_price;
            $qty   = fake()->numberBetween(1, 3);

            return [
                'product_id'   => $product->id,
                'unit_price'   => $price,
                'product_sku'  => $product->sku,
                'product_name' => $product->name,
                'qty'          => $qty,
                'row_total'    => $qty * $price,
            ];
        });
    }

}
