<?php

namespace App\Models\Inventory;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    /** @use HasFactory<\Database\Factories\Inventory\InventoryFactory> */
    use HasFactory;
    protected $fillable = [
        'product_id', 'location_id',
        'qty_on_hand', 'qty_reserved', 'safety_stock',
    ];

    protected $casts = [
        'qty_on_hand'  => 'integer',
        'qty_reserved' => 'integer',
        'safety_stock' => 'integer',
    ];

    protected $appends = ['qty_available'];

    /** Relationships */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    /** Accessors */
    public function getQtyAvailableAttribute(): int
    {
        return max(0, (int)$this->qty_on_hand - (int)$this->qty_reserved);
    }

    /** Scopes */
    public function scopeForProduct($q, int $productId) { return $q->where('product_id', $productId); }
    public function scopeAtLocation($q, int $locationId) { return $q->where('location_id', $locationId); }
    public function scopeBelowSafety($q) {
        return $q->whereRaw('(qty_on_hand - qty_reserved) <= safety_stock');
    }

}
