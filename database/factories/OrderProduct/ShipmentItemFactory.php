<?php

namespace Database\Factories\OrderProduct;

use App\Models\OrderProduct\OrderItem;
use App\Models\OrderProduct\Shipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderProduct\ShipmentItem>
 */
class ShipmentItemFactory extends Factory
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
            'shipment_id'  => Shipment::factory()->for($oi->order)->create()->id,
            'order_item_id'=> $oi->id,
            'qty'          => $oi->qty,
        ];
    }

    public function forShipment(Shipment $s, OrderItem $oi, int $qty = null): self
    {
        return $this->state(fn () => [
            'shipment_id'   => $s->id,
            'order_item_id' => $oi->id,
            'qty'           => $qty ?? $oi->qty,
        ]);
    }

}
