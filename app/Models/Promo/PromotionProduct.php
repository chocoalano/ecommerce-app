<?php

namespace App\Models\Promo;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionProduct extends Model
{
    /** @use HasFactory<\Database\Factories\Promo\PromotionProductFactory> */
    use HasFactory;
    protected $fillable = [
        'promotion_id','product_id','min_qty',
        'discount_value','discount_percent','bundle_price',
    ];

    protected $casts = [
        'min_qty'         => 'integer',
        'discount_value'  => 'decimal:2',
        'discount_percent'=> 'decimal:2',
        'bundle_price'    => 'decimal:2',
    ];

    /** Relationships */
    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class); // nullable (promo global)
    }

    /** Helpers: hitung harga setelah promo untuk sebuah produk */
    public function discountFor(float $unitPrice, int $qty = 1): float
    {
        if ($qty < ($this->min_qty ?? 1)) {
            return 0.0;
        }

        $type = $this->promotion->type ?? null;

        return match ($type) {
            Promotion::TYPE_PERCENT => round($unitPrice * ($this->discount_percent ?? 0) / 100, 2) * $qty,
            Promotion::TYPE_FIXED   => round(($this->discount_value ?? 0), 2) * $qty,
            Promotion::TYPE_BUNDLE  => max(0, round(($unitPrice * $qty) - ($this->bundle_price ?? 0), 2)),
            default                 => 0.0, // tipe lain dihitung di service sesuai kebutuhan
        };
    }

}
