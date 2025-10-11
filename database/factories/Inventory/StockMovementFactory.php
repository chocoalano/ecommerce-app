<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\InventoryLocation;
use App\Models\Inventory\StockMovement;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory\StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement([
            StockMovement::TYPE_IN,
            StockMovement::TYPE_OUT,
            StockMovement::TYPE_RESERVE,
            StockMovement::TYPE_RELEASE,
            StockMovement::TYPE_ADJUST,
        ]);

        // qty logic: IN/OUT/RESERVE/RELEASE gunakan nilai absolut positif; ADJUST boleh plus/minus
        $baseQty = $this->faker->numberBetween(1, 25);
        $qty = $type === StockMovement::TYPE_ADJUST
            ? $this->faker->numberBetween(-15, 15)
            : $baseQty;

        return [
            'product_id'  => Product::factory(),
            'location_id' => InventoryLocation::factory(), // boleh diubah ke null() state
            'type'        => $type,
            'qty'         => $qty,
            'ref_type'    => $this->faker->optional()->randomElement(['ORDER', 'RETURN', 'MANUAL']),
            'ref_id'      => $this->faker->optional()->numberBetween(1, 10000),
            'note'        => $this->faker->optional()->sentence(6),
        ];
    }

    public function atLocation(InventoryLocation $loc): self
    {
        return $this->state(fn () => ['location_id' => $loc->id]);
    }

    public function forProduct(int $productId): self
    {
        return $this->state(fn () => ['product_id' => $productId]);
    }

    public function withoutLocation(): self
    {
        return $this->state(fn () => ['location_id' => null]);
    }

    public function type(string $type): self
    {
        return $this->state(fn () => ['type' => $type]);
    }

}
