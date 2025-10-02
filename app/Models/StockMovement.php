<?php

namespace App\Models;

use App\Models\BaseModel;

class StockMovement extends BaseModel
{
    
    protected $table = 'stock_movements';
    public $timestamps = false;
    protected $fillable = [
        'variant_id','location_id','type','qty','ref_type','ref_id','note'
    ];
    protected $casts = [
        'id' => 'integer',
        'variant_id' => 'integer',
        'location_id' => 'integer',
        'qty' => 'integer',
        'ref_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function ref()
    {
        return $this->belongsTo(Ref::class);
    }

}