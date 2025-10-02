<?php

namespace App\Models;

use App\Models\BaseModel;

class OrderItem extends BaseModel
{
    
    protected $table = 'order_items';
    public $timestamps = false;
    protected $fillable = [
        'order_id','product_id','variant_id','name','sku','qty','unit_price','discount_amount','row_total','meta_json'
    ];
    protected $casts = [
        'id' => 'integer',
        'order_id' => 'integer',
        'product_id' => 'integer',
        'variant_id' => 'integer',
        'qty' => 'integer',
        'unit_price' => 'float',
        'discount_amount' => 'float',
        'row_total' => 'float',
        'meta_json' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    public function returnItems()
    {
        return $this->hasMany(ReturnItem::class, 'order_item_id');
    }

    public function shipmentItems()
    {
        return $this->hasMany(ShipmentItem::class, 'order_item_id');
    }

}