<?php

namespace App\Models;

use App\Models\BaseModel;

class ShipmentItem extends BaseModel
{
    
    protected $table = 'shipment_items';
    public $timestamps = false;
    protected $fillable = [
        'shipment_id','order_item_id','qty'
    ];
    protected $casts = [
        'id' => 'integer',
        'shipment_id' => 'integer',
        'order_item_id' => 'integer',
        'qty' => 'integer',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

}