<?php

namespace App\Models\Inventory;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLocation extends Model
{
    /** @use HasFactory<\Database\Factories\Inventory\InventoryLocationFactory> */
    use HasFactory;
    protected $fillable = [
        'code', 'name', 'address_json', 'is_active',
    ];

    protected $casts = [
        'address_json' => 'array',
        'is_active'    => 'boolean',
    ];

    /** Relationships */
    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'location_id');
    }

    public function products()
    {
        // via pivot inventories
        return $this->belongsToMany(Product::class, 'inventories', 'location_id', 'product_id')
            ->withPivot(['qty_on_hand', 'qty_reserved', 'safety_stock'])
            ->withTimestamps();
    }

    /** Scopes */
    public function scopeActive($q) { return $q->where('is_active', true); }

}
