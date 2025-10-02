<?php

namespace App\Models;

use App\Models\BaseModel;

class ProductMedia extends BaseModel
{
    public $timestamps = false; // created_at only (useCurrent)
    protected $table = 'product_media';

    protected $fillable = [
        'product_id','url','type','alt_text','sort_order','is_primary','created_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
