<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // contoh atribut spesifik varian, bisa kosong juga
        $attrs = $this->faker->randomElement([
            ['size' => 'Small',  'color' => 'Green'],
            ['size' => 'Medium', 'color' => 'Black'],
            ['size' => 'Large',  'color' => 'White'],
            ['bundle' => '3x'],
            null,
        ]);

        return [
            'product_id'      => null, // set di seeder/relasi: ->for(Product::factory()) atau manual
            'variant_sku'     => strtoupper($this->faker->bothify('VAR-#####')),
            'name'            => $this->faker->randomElement(['Default', 'Small', 'Medium', 'Large', 'Bundle 3x']),
            'attributes_json' => $attrs,               // cast: array
            'base_price'      => $this->faker->randomFloat(2, 20000, 1500000),
            'currency'        => 'IDR',
            'weight_gram'     => $this->faker->numberBetween(100, 2000),
            'length_mm'       => $this->faker->numberBetween(50, 400),
            'width_mm'        => $this->faker->numberBetween(50, 400),
            'height_mm'       => $this->faker->numberBetween(10, 300),
            'is_active'       => $this->faker->boolean(90),      // cast: integer (0/1)
            'stock'       => $this->faker->numberBetween(10, 300),
        ];
    }

    /**
     * State: non-aktif.
     */
    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => 0]);
    }

    /**
     * State: dengan atribut tertentu (override cepat).
     */
    public function withAttributes(array $attributes): static
    {
        return $this->state(fn () => ['attributes_json' => $attributes]);
    }
}
