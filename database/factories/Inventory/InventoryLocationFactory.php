<?php

namespace Database\Factories\Inventory;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory\InventoryLocation>
 */
class InventoryLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = 'WH-' . strtoupper(Str::random(3)) . '-' . $this->faker->numberBetween(1, 99);

        return [
            'code'        => $code,
            'name'        => $this->faker->company() . ' Warehouse',
            'address_json'=> [
                'street' => $this->faker->streetAddress(),
                'city'   => $this->faker->city(),
                'prov'   => $this->faker->state(),
                'zip'    => $this->faker->postcode(),
                'lat'    => $this->faker->latitude(-11, 6),
                'lng'    => $this->faker->longitude(95, 141),
            ],
            'is_active'   => true,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn () => ['is_active' => false]);
    }

}
