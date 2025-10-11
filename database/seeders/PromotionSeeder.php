<?php

namespace Database\Seeders;

use App\Models\Product\Product;
use App\Models\Promo\Promotion;
use App\Models\Promo\PromotionProduct;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat 12 produk katalog
    $products = Product::factory()->count(12)->create();

    // Promo persen 15% live
    $promoPercent = Promotion::factory()->percent(15)->create([
        'code' => 'DISC15',
        'type' => 'PERCENT_DISCOUNT',
        'priority' => 10,
    ]);

    // Apply ke 5 produk acak
    $products->random(5)->each(function ($p) use ($promoPercent) {
        PromotionProduct::factory()
            ->forPromotion($promoPercent)
            ->forProduct($p)
            ->percent(15)
            ->create();
    });

    // Promo fixed 20k
    $promoFixed = Promotion::factory()->fixed(20000)->create([
        'code' => 'LESS20K',
        'priority' => 20,
    ]);

    $products->random(4)->each(function ($p) use ($promoFixed) {
        PromotionProduct::factory()
            ->forPromotion($promoFixed)
            ->forProduct($p)
            ->fixed(20000)
            ->create();
    });

    // Promo bundle (global) — logic harga bundle dihitung di service saat checkout
    $promoBundle = Promotion::factory()->bundle()->create([
        'code' => 'WEEKEND-BUNDLE',
        'priority' => 5,
    ]);

    PromotionProduct::factory()
        ->forPromotion($promoBundle)
        ->global()
        ->bundle(99000)
        ->create(['min_qty' => 2]);
    }
}
