<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMedia extends Model
{
    /** @use HasFactory<\Database\Factories\Product\ProductMediaFactory> */
    use HasFactory;
    protected $fillable = [
        'product_id','url','type','alt_text','sort_order','is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
