<?php

namespace App\Models;

use App\Models\BaseModel;

class CartItem extends BaseModel
{
    
    protected $table = 'cart_items';
    public $timestamps = false;
    protected $fillable = [
        'cart_id','variant_id','qty','unit_price','row_total','meta_json'
    ];
    protected $casts = [
        'id' => 'integer',
        'cart_id' => 'integer',
        'variant_id' => 'integer',
        'qty' => 'integer',
        'unit_price' => 'float',
        'row_total' => 'float',
        'meta_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

}