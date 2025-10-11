<?php

namespace Database\Factories\OfferContent;

use App\Models\OfferContent\Landing;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<OfferContent\LandingProduct>
 */
class LandingProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'landing_id' => Landing::factory(),
            'product_id' => Product::factory(),
            'sort_order' => $this->faker->numberBetween(0, 50),
        ];
    }

    public function forLanding(Landing $landing): self
    {
        return $this->state(fn () => ['landing_id' => $landing->id]);
    }

    public function forProduct(int|Product $product): self
    {
        return $this->state(function () use ($product) {
            return ['product_id' => $product instanceof Product ? $product->id : $product];
        });
    }

    public function ordered(int $order): self
    {
        return $this->state(fn () => ['sort_order' => $order]);
    }

}
