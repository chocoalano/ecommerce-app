<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
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
        $name = $this->faker->unique()->words(3, true);

        return [
            'sku' => strtoupper($this->faker->bothify('PRD-#####')),
            'slug' => Str::slug($name . '-' . $this->faker->unique()->numberBetween(100, 999)),
            'name' => ucwords($name),
            'short_desc' => $this->faker->sentence(8, true),
            'long_desc' => $this->faker->paragraphs(3, true),
            'brand' => $this->faker->randomElement(['SAS', 'YourVibe', 'Miwa', 'Generic']),
            'warranty_months' => $this->faker->optional(0.7)->numberBetween(0, 24), // 70% ada garansi
            'is_active' => $this->faker->boolean(85), // 85% aktif
        ];
    }
}
