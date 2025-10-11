<?php

namespace Database\Factories\OfferContent;

use App\Models\OfferContent\Landing;
use App\Models\Product\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<OfferContent\LandingCategory>
 */
class LandingCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'landing_id'  => Landing::factory(),
            'category_id' => Category::factory(),
            'sort_order'  => $this->faker->numberBetween(0, 50),
        ];
    }

    public function forLanding(Landing $landing): self
    {
        return $this->state(fn () => ['landing_id' => $landing->id]);
    }

    public function forCategory(int|Category $category): self
    {
        return $this->state(function () use ($category) {
            return ['category_id' => $category instanceof Category ? $category->id : $category];
        });
    }

    public function ordered(int $order): self
    {
        return $this->state(fn () => ['sort_order' => $order]);
    }

}
