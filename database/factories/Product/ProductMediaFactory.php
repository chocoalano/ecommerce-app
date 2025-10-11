<?php

namespace Database\Factories\Product;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product\ProductMedia>
 */
class ProductMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'url'        => 'https://picsum.photos/seed/' . Str::random(8) . '/800/800',
            'type'       => 'image',
            'alt_text'   => $this->faker->sentence(3),
            'sort_order' => $this->faker->numberBetween(0, 5),
            'is_primary' => false,
        ];
    }

    public function primary(): self
    {
        return $this->state(fn () => ['is_primary' => true, 'sort_order' => 0]);
    }

}
