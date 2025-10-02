<?php

namespace App\Models;

use App\Models\BaseModel;

class PromotionProduct extends BaseModel
{

    protected $table = 'promotion_products';
    public $timestamps = false;
    protected $fillable = [
        'promotion_id','product_id','variant_id','min_qty','discount_value','discount_percent','bundle_price'
    ];
    protected $casts = [
        'id' => 'integer',
        'promotion_id' => 'integer',
        'product_id' => 'integer',
        'variant_id' => 'integer',
        'min_qty' => 'integer',
        'discount_value' => 'float',
        'discount_percent' => 'float',
        'bundle_price' => 'float',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

}
