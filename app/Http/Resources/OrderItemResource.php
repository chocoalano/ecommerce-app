<?php
// app/Http/Resources/OrderItemResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'order_id'        => $this->order_id,
            'product_id'      => $this->product_id,
            'name'            => $this->name,
            'sku'             => $this->sku,
            'qty'             => (int) $this->qty,
            'unit_price'      => $this->unit_price,
            'discount_amount' => $this->discount_amount,
            'row_total'       => $this->row_total,
            'weight_gram'     => $this->weight_gram,
            'length_mm'       => $this->length_mm,
            'width_mm'        => $this->width_mm,
            'height_mm'       => $this->height_mm,
            'meta_json'       => $this->meta_json,
            'created_at'      => optional($this->created_at)->toISOString(true),
            'updated_at'      => optional($this->updated_at)->toISOString(true),

            'product'         => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
