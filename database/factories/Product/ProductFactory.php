<?php

namespace Database\Factories\Product;

use App\Models\Product\Category;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->sentence() ?? $this->faker->unique()->words(3, true); // productName if present

        return [
            'sku'             => 'SKU-' . strtoupper(Str::random(10)),
            'slug'            => Str::slug($name) . '-' . Str::random(6),
            'name'            => ucwords($name),
            'short_desc'      => $this->faker->sentence(12),
            'long_desc'       => $this->faker->paragraphs(2, true),
            'brand'           => $this->faker->company(),
            'warranty_months' => $this->faker->numberBetween(3, 24),

            'base_price'      => $this->faker->randomFloat(2, 10000, 2500000),
            'currency'        => 'IDR',
            'stock'           => $this->faker->numberBetween(0, 500),

            'weight_gram'     => $this->faker->numberBetween(50, 5000),
            'length_mm'       => $this->faker->numberBetween(50, 400),
            'width_mm'        => $this->faker->numberBetween(50, 400),
            'height_mm'       => $this->faker->numberBetween(10, 300),

            'is_active'       => $this->faker->boolean(95),
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    /**
     * Pasang kategori tertentu (opsional).
     */
    public function withCategories(int $count = 1): self
    {
        return $this->afterCreating(function (Product $product) use ($count) {
            $cats = Category::query()->inRandomOrder()->limit($count)->pluck('id');
            if ($cats->isEmpty()) {
                $cats = Category::factory()->count($count)->create()->pluck('id');
            }
            $product->categories()->syncWithoutDetaching($cats->all());
        });
    }

    /**
     * Tambahkan media dummy pada produk.
     */
    public function withMedia(int $images = 3): self
    {
        return $this->afterCreating(function (Product $product) use ($images) {
            // buat media, tandai satu sebagai primary
            $primaryIndex = 0;
            for ($i = 0; $i < $images; $i++) {
                $product->media()->create([
                    'url'        => 'https://picsum.photos/seed/' . Str::random(8) . '/800/800',
                    'type'       => 'image',
                    'alt_text'   => $product->name . ' Image ' . ($i + 1),
                    'sort_order' => $i,
                    'is_primary' => $i === $primaryIndex,
                ]);
            }
        });
    }

}
