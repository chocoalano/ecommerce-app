<?php

namespace App\Models;

use App\Models\BaseModel;

class ProductVariant extends BaseModel
{
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id','variant_sku','name','attributes_json',
        'base_price','currency','weight_gram','length_mm','width_mm','height_mm','is_active',
    ];

    protected $casts = [
        'attributes_json' => 'array',
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function media()
    {
        return $this->hasMany(ProductVariantMedia::class, 'variant_id', 'id');
    }
}
