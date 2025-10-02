<?php

namespace App\Models;

use App\Models\BaseModel;

class Courier extends BaseModel
{
    
    protected $table = 'couriers';
    public $timestamps = false;
    protected $fillable = [
        'code','name','is_active'
    ];
    protected $casts = [
        'id' => 'integer',
        'is_active' => 'integer',
    ];

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'courier_id');
    }

}