<?php

namespace Database\Factories\Promo;

use App\Models\Product\Product;
use App\Models\Promo\Promotion;
use App\Models\Promo\PromotionProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promo\PromotionProduct>
 */
class PromotionProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'promotion_id'    => Promotion::factory(),
            'product_id'      => Product::factory(), // boleh di-null-kan via state global()
            'min_qty'         => $this->faker->numberBetween(1, 3),
            'discount_value'  => $this->faker->numberBetween(5000, 50000),
            'discount_percent'=> $this->faker->numberBetween(5, 30),
            'bundle_price'    => $this->faker->numberBetween(25000, 150000),
        ];
    }

    /** Selaraskan nilai diskon dengan tipe Promo terkait */
    public function configure()
    {
        return $this->afterMaking(function (PromotionProduct $pp) {
            $promo = $pp->promotion ?? Promotion::find($pp->promotion_id);
            if (!$promo) return;

            switch ($promo->type) {
                case 'PERCENT_DISCOUNT':
                    $pp->discount_percent = $pp->discount_percent ?? mt_rand(5, 30);
                    $pp->discount_value = null; $pp->bundle_price = null;
                    break;

                case 'FIXED_DISCOUNT':
                    $pp->discount_value = $pp->discount_value ?? mt_rand(5000, 50000);
                    $pp->discount_percent = null; $pp->bundle_price = null;
                    break;

                case 'BUNDLE_PRICE':
                    // bundle price akan di-set saat tahu harga produk; isi placeholder dulu
                    $pp->bundle_price = $pp->bundle_price ?? mt_rand(25000, 150000);
                    $pp->discount_percent = null; $pp->discount_value = null;
                    break;

                default:
                    // jenis lain: biarkan kosong (hitung di service)
                    $pp->discount_percent = null;
                    $pp->discount_value   = null;
                    $pp->bundle_price     = null;
            }
        });
    }

    /** States & helpers */
    public function forPromotion(Promotion $promotion): self
    {
        return $this->state(fn () => ['promotion_id' => $promotion->id]);
    }

    public function forProduct(Product $product): self
    {
        return $this->state(fn () => ['product_id' => $product->id]);
    }

    // Promo global (tidak spesifik product_id)
    public function global(): self
    {
        return $this->state(fn () => ['product_id' => null]);
    }

    public function percent(int $percent): self
    {
        return $this->state(fn () => [
            'discount_percent' => $percent,
            'discount_value'   => null,
            'bundle_price'     => null,
        ]);
    }

    public function fixed(int $value): self
    {
        return $this->state(fn () => [
            'discount_value'   => $value,
            'discount_percent' => null,
            'bundle_price'     => null,
        ]);
    }

    public function bundle(int $price): self
    {
        return $this->state(fn () => [
            'bundle_price'     => $price,
            'discount_value'   => null,
            'discount_percent' => null,
        ]);
    }

}
