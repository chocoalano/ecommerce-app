<?php
// app/Http/Resources/AddressResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'customer_id'   => $this->customer_id,
            'label'         => $this->label,
            'recipient_name'=> $this->recipient_name,
            'phone'         => $this->phone,
            'line1'         => $this->line1,
            'line2'         => $this->line2,
            'city'          => $this->city,
            'province'      => $this->province,
            'postal_code'   => $this->postal_code,
            'country'       => $this->country,
            'is_default'    => (bool) $this->is_default,
            'created_at'    => optional($this->created_at)->toISOString(true),
            'updated_at'    => optional($this->updated_at)->toISOString(true),
        ];
    }
}
