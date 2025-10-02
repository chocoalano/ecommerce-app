<?php

namespace App\Models;

use App\Models\BaseModel;

class WishlistItem extends BaseModel
{
    
    protected $table = 'wishlist_items';
    public $timestamps = false;
    protected $fillable = [
        'wishlist_id','product_id','variant_id'
    ];
    protected $casts = [
        'id' => 'integer',
        'wishlist_id' => 'integer',
        'product_id' => 'integer',
        'variant_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function wishlist()
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

}