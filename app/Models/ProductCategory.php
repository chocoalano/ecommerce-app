<?php

namespace App\Models;

use App\Models\BaseModel;

class ProductCategory extends BaseModel
{
    
    protected $table = 'product_categories';
    public $timestamps = false;
    protected $fillable = [
        'product_id','category_id'
    ];
    protected $casts = [
        'product_id' => 'integer',
        'category_id' => 'integer',
    ];
    // Pivot table detected: consider defining belongsToMany() on the related models.

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}