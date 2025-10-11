<?php

namespace Database\Factories\OrderProduct;

use App\Models\OrderProduct\OrderItem;
use App\Models\OrderProduct\OrderReturn;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderProduct\ReturnItem>
 */
class ReturnItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $oi = OrderItem::factory()->create();

        return [
            'return_id'    => OrderReturn::factory()->for($oi->order)->create()->id,
            'order_item_id'=> $oi->id,
            'qty'          => 1,
            'condition_note'=> $this->faker->optional()->sentence(4),
        ];
    }

    public function forReturn(OrderReturn $r, OrderItem $oi, int $qty = 1): self
    {
        return $this->state(fn () => [
            'return_id'     => $r->id,
            'order_item_id' => $oi->id,
            'qty'           => $qty,
        ]);
    }

}
