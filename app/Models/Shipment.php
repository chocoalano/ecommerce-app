<?php

namespace App\Models;

use App\Models\BaseModel;

class Shipment extends BaseModel
{
    
    protected $table = 'shipments';
    protected $fillable = [
        'order_id','courier_id','tracking_no','status','shipped_at','delivered_at','shipping_fee'
    ];
    protected $casts = [
        'id' => 'integer',
        'order_id' => 'integer',
        'courier_id' => 'integer',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'shipping_fee' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    public function shipmentItems()
    {
        return $this->hasMany(ShipmentItem::class, 'shipment_id');
    }

}