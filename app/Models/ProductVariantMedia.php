<?php

namespace App\Models;

use App\Models\BaseModel;

class ProductVariantMedia extends BaseModel
{

    public $timestamps = false; // created_at only (useCurrent)
    protected $table = 'product_variant_media';

    protected $fillable = [
        'variant_id','url','type','alt_text','sort_order','is_primary','created_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
