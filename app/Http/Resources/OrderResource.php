<?php
// app/Http/Resources/OrderResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,
            'order_no'           => $this->order_no,
            'customer_id'        => $this->customer_id,
            'currency'           => $this->currency,
            'status'             => strtoupper($this->status),
            'subtotal_amount'    => $this->subtotal_amount,
            'discount_amount'    => $this->discount_amount,
            'shipping_amount'    => $this->shipping_amount,
            'tax_amount'         => $this->tax_amount,
            'grand_total'        => $this->grand_total,
            'shipping_address_id'=> $this->shipping_address_id,
            'billing_address_id' => $this->billing_address_id,
            'applied_promos'     => $this->applied_promos,
            'notes'              => $this->notes,
            'placed_at'          => optional($this->placed_at)->toISOString(true),
            'created_at'         => optional($this->created_at)->toISOString(true),
            'updated_at'         => optional($this->updated_at)->toISOString(true),

            'items'              => OrderItemResource::collection($this->whenLoaded('items')),
            'shipping_address'   => new AddressResource($this->whenLoaded('shippingAddress')),
        ];
    }
}
