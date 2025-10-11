<?php

namespace App\Models\Product;

use App\Models\Promo\Promotion;
use App\Models\Promo\PromotionProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\Product\ProductFactory> */
    use HasFactory;
    protected $fillable = [
        'sku','slug','name','short_desc','long_desc','brand','warranty_months',
        'base_price','currency','stock',
        'weight_gram','length_mm','width_mm','height_mm',
        'is_active',
    ];

    protected $casts = [
        'base_price'     => 'decimal:2',
        'stock'          => 'integer',
        'warranty_months'=> 'integer',
        'weight_gram'    => 'integer',
        'length_mm'      => 'integer',
        'width_mm'       => 'integer',
        'height_mm'      => 'integer',
        'is_active'      => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (blank($product->slug) && filled($product->name)) {
                $product->slug = Str::slug($product->name) . '-' . Str::random(6);
            }
        });
    }

    /* Relationships */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function media()
    {
        return $this->hasMany(ProductMedia::class)->orderBy('sort_order');
    }

    public function image()
    {
        return $this->hasOne(ProductMedia::class)->orderBy('sort_order');
    }

    public function primaryMedia()
    {
        return $this->hasOne(ProductMedia::class)->where('is_primary', true);
    }

    /* Accessors */
    public function getPrimaryImageUrlAttribute(): ?string
    {
        $primary = $this->primaryMedia()->first();
        if ($primary) return $primary->url;
        $first = $this->media()->first();
        return $first?->url;
    }

    /* Scopes */
    public function scopeActive($q) { return $q->where('is_active', true); }

    public function promotionItems() { return $this->hasMany(PromotionProduct::class); }
    public function promotions() {
    return $this->belongsToMany(Promotion::class, 'promotion_products')
        ->withPivot(['min_qty','discount_value','discount_percent','bundle_price'])
        ->withTimestamps();
    }

    public function reviews() { return $this->hasMany(ProductReview::class); }
    public function approvedReviews() { return $this->reviews()->where('is_approved', true); }

    // Cart relationships
    public function cartItems() { return $this->hasMany(\App\Models\CartProduct\CartItem::class); }
    public function carts() {
        return $this->belongsToMany(\App\Models\CartProduct\Cart::class, 'cart_items')
                    ->withPivot(['qty', 'unit_price', 'row_total', 'product_sku', 'product_name', 'meta_json'])
                    ->withTimestamps();
    }

    // Cart helper methods
    public function isInCart(\App\Models\CartProduct\Cart $cart): bool
    {
        return $cart->items()->where('product_id', $this->id)->exists();
    }

    public function getCartQuantity(\App\Models\CartProduct\Cart $cart): int
    {
        $item = $cart->items()->where('product_id', $this->id)->first();
        return $item ? $item->qty : 0;
    }

}
