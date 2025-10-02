<?php

namespace App\Models;

use App\Models\BaseModel;

class Inventory extends BaseModel
{
    
    protected $table = 'inventories';
    public $timestamps = false;
    protected $fillable = [
        'variant_id','location_id','qty_on_hand','qty_reserved','safety_stock'
    ];
    protected $casts = [
        'id' => 'integer',
        'variant_id' => 'integer',
        'location_id' => 'integer',
        'qty_on_hand' => 'integer',
        'qty_reserved' => 'integer',
        'safety_stock' => 'integer',
        'updated_at' => 'datetime',
    ];

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

}