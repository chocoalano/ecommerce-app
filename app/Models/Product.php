<?php

namespace App\Models;

use App\Models\BaseModel;

class Product extends BaseModel
{

    protected $table = 'products';
    protected $fillable = [
        'sku','slug','name','short_desc','long_desc','brand','warranty_months','is_active'
    ];
    protected $casts = [
        'id' => 'integer',
        'warranty_months' => 'integer',
        'is_active' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function productCategories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function productMedia()
    {
        return $this->hasMany(ProductMedia::class, 'product_id');
    }

    public function productReviews()
    {
        return $this->hasMany(ProductReview::class, 'product_id');
    }

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function promotionProducts()
    {
        return $this->hasMany(PromotionProduct::class, 'product_id');
    }

    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class, 'product_id');
    }

}
