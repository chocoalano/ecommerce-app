<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\InventoryLocation;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $onHand   = $this->faker->numberBetween(0, 200);
        $reserved = $this->faker->numberBetween(0, (int)($onHand / 2));

        return [
            'product_id'   => Product::factory(),
            'location_id'  => InventoryLocation::factory(),
            'qty_on_hand'  => $onHand,
            'qty_reserved' => $reserved,
            'safety_stock' => $this->faker->numberBetween(0, 20),
        ];
    }

    public function zero(): self
    {
        return $this->state(fn () => [
            'qty_on_hand' => 0, 'qty_reserved' => 0,
        ]);
    }

}
