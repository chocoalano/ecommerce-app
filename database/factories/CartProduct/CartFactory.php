<?php

namespace Database\Factories\CartProduct;

use App\Models\Auth\Customer;
use App\Models\CartProduct\Cart;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartProduct\Cart>
 */
class CartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id'     => null,
            'session_id'      => Str::uuid()->toString(),
            'currency'        => 'IDR',
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'tax_amount'      => 0,
            'grand_total'     => 0,
            'applied_promos'  => null,
        ];
    }

    /** State: cart milik customer (bukan guest) */
    public function forCustomer(?Customer $customer = null): self
    {
        return $this->state(fn () => [
            'customer_id' => ($customer?->id) ?? Customer::factory(),
            'session_id'  => null,
        ]);
    }

    /** Isi cart dengan N item unik */
    public function withItems(int $count = 2): self
    {
        return $this->afterCreating(function (Cart $cart) use ($count) {
            // ambil produk unik
            $products = Product::inRandomOrder()->limit($count)->get();
            if ($products->count() < $count) {
                $products = Product::factory()->count($count)->create();
            }

            foreach ($products as $p) {
                $qty  = fake()->numberBetween(1, 3);
                $price = (float)$p->base_price;

                $cart->items()->create([
                    'product_id'   => $p->id,
                    'qty'          => $qty,
                    'unit_price'   => $price,
                    'currency'     => $cart->currency ?? 'IDR',
                    'product_sku'  => $p->sku,
                    'product_name' => $p->name,
                    'row_total'    => $qty * $price, // tanpa diskon item
                ]);
            }

            $cart->recalcTotals();
        });
    }

}
