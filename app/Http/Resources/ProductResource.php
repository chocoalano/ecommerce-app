<?php
// app/Http/Resources/ProductResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'sku'             => $this->sku,
            'slug'            => $this->slug,
            'name'            => $this->name,
            'short_desc'      => $this->short_desc,
            'long_desc'       => $this->long_desc,
            'brand'           => $this->brand,
            'warranty_months' => (int) $this->warranty_months,
            'base_price'      => $this->base_price,
            'currency'        => $this->currency,
            'stock'           => (int) $this->stock,
            'weight_gram'     => $this->weight_gram,
            'length_mm'       => $this->length_mm,
            'width_mm'        => $this->width_mm,
            'height_mm'       => $this->height_mm,
            'is_active'       => (bool) $this->is_active,
            'created_at'      => optional($this->created_at)->toISOString(true),
            'updated_at'      => optional($this->updated_at)->toISOString(true),
        ];
    }
}
