<?php

namespace Database\Factories\Product;

use App\Models\Product\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'parent_id'   => null,
            'slug'        => Str::slug($name) . '-' . Str::random(6),
            'name'        => ucwords($name),
            'description' => $this->faker->optional()->sentence(8),
            'sort_order'  => $this->faker->numberBetween(0, 100),
            'is_active'   => $this->faker->boolean(90),
            'image'       => 'storage/images/galaxy-z-flip7-share-image.png',
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function childOf(?Category $parent = null): self
    {
        return $this->state(function () use ($parent) {
            return ['parent_id' => $parent?->id ?? Category::factory()];
        });
    }

}
