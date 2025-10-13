<?php

namespace App\Models\Promo;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    /** @use HasFactory<\Database\Factories\Promo\PromotionFactory> */
    use HasFactory;

    public const TYPE = [
        'PERCENT_DISCOUNT'        => 'Diskon Persentase',
        'FIXED_DISCOUNT'          => 'Diskon Nominal',
        'CASHBACK'                => 'Cashback',
        'BUNDLE_PRICE'            => 'Harga Bundling',
        'GIFT_WITH_PURCHASE'      => 'Hadiah Pembelian',
        'BANK_INSTALLMENT'        => 'Cicilan Bank',
        'PAYMENT_METHOD_DISCOUNT' => 'Diskon Metode Bayar',
        'FLASH_SALE'              => 'Flash Sale',
        'TRADE_IN'                => 'Tukar Tambah',
    ];

    public const SHOW_ON = [
        'HERO'   => 'Hero',
        'BANNER' => 'Banner',
    ];

    public const PAGE = [
        'beranda' => 'Beranda',
    ];

    protected $fillable = [
        'code','name','type','landing_slug','description','image',
        'start_at','end_at','is_active','priority',
        'max_redemption','per_user_limit','conditions_json',
        'show_on','custom_html','page',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'start_at'        => 'datetime',
        'end_at'          => 'datetime',
        'conditions_json' => 'array',
    ];

    /** Relationships */
    public function promotionProducts()
    {
        return $this->hasMany(PromotionProduct::class);
    }

    // Convenience: ambil products lewat pivot detail (dengan pivot fields)
    public function products()
    {
        return $this->belongsToMany(Product::class, 'promotion_products')
            ->withPivot(['min_qty','discount_value','discount_percent','bundle_price'])
            ->with(['media'])
            ->withTimestamps();
    }

    /** Scopes */
    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeLive($q)
    {
        $now = now();
        return $q->where('start_at', '<=', $now)->where('end_at', '>=', $now);
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('priority')->orderBy('start_at', 'desc');
    }

    /** Helpers */
    public function isLive(): bool
    {
        $now = now();
        return $this->is_active && $this->start_at <= $now && $this->end_at >= $now;
    }
}
