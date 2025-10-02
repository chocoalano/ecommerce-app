<?php

namespace App\Models;

use App\Models\BaseModel;

class InventoryLocation extends BaseModel
{
    
    protected $table = 'inventory_locations';
    public $timestamps = false;
    protected $fillable = [
        'code','name','address_json','is_active'
    ];
    protected $casts = [
        'id' => 'integer',
        'address_json' => 'array',
        'is_active' => 'integer',
        'created_at' => 'datetime',
    ];

}