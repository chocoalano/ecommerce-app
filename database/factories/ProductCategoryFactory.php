<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        return [
            'sku' => strtoupper($this->faker->bothify('PRD-#####')),
            'name' => ucwords($name),
            'slug' => str($name)->slug(),
            'description' => $this->faker->paragraphs(2, true),
            'is_active' => $this->faker->boolean(85),
            'brand' => $this->faker->randomElement(['SAS', 'YourVibe', 'Miwa']),
            'weight_gram' => $this->faker->numberBetween(100, 1500),
            'volume_ml' => $this->faker->optional()->numberBetween(0, 1000),
            'category_id' => null, // set in seeder with relation
        ];
    }
}
