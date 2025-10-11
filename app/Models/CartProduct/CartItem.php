<?php

namespace App\Models\CartProduct;

use App\Models\Auth\Customer;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    /** @use HasFactory<\Database\Factories\CartProduct\CartItemFactory> */
    use HasFactory;
    protected $fillable = [
        'cart_id',
        'product_id',
        'qty',
        'unit_price',
        'currency',
        'product_sku',
        'product_name',
        'row_total',
        'promo_code',
        'bundle_name',
        'note',
    ];

    protected $casts = [
        'qty'    => 'integer',
        'unit_price'       => 'decimal:2',
        'row_total' => 'decimal:2',
    ];

    /* Relationships */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customer()
    {
        return $this->hasOneThrough(Customer::class, Cart::class, 'id', 'id', 'cart_id', 'customer_id');
    }

    /* Calculation Methods */

    /**
     * Get formatted unit price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->unit_price, 0, ',', '.');
    }

    /**
     * Get formatted row total
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->row_total, 0, ',', '.');
    }

    /**
     * Get subtotal before any item-level discounts
     */
    public function getSubtotalAttribute(): float
    {
        return $this->qty * (float) $this->unit_price;
    }

    /**
     * Get item discount amount
     */
    public function getDiscountAmountAttribute(): float
    {
        return max(0, $this->subtotal - (float) $this->row_total);
    }

    /**
     * Check if item has discount
     */
    public function hasDiscount(): bool
    {
        return $this->discount_amount > 0;
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentageAttribute(): float
    {
        if ($this->subtotal <= 0) return 0;
        return round(($this->discount_amount / $this->subtotal) * 100, 2);
    }

    /**
     * Get total weight for this item
     */
    public function getTotalWeightAttribute(): int
    {
        return $this->qty * ($this->product->weight_gram ?? 0);
    }

    /**
     * Update qty and recalculate totals
     */
    public function updateqty(int $qty): void
    {
        if ($qty <= 0) {
            $this->delete();
            return;
        }

        $this->update([
            'qty' => $qty,
            'row_total' => $qty * (float) $this->unit_price,
        ]);
    }

    /**
     * Increment qty
     */
    public function incrementqty(int $amount = 1): void
    {
        $this->updateqty($this->qty + $amount);
    }

    /**
     * Decrement qty
     */
    public function decrementqty(int $amount = 1): void
    {
        $this->updateqty($this->qty - $amount);
    }

    /* Hooks: snapshot & total */
    protected static function booted(): void
    {
        static::creating(function (CartItem $item) {
            if ($item->product && blank($item->product_sku)) {
                $item->product_sku  = $item->product->sku;
                $item->product_name = $item->product->name;
            }
            $item->currency = $item->currency ?: ($item->cart->currency ?? 'IDR');
            $item->unit_price = $item->unit_price ?? (float)optional($item->product)->base_price ?? 0.0;
            $item->row_total = $item->row_total ?? ($item->qty * $item->unit_price);
        });

        static::saved(function (CartItem $item) {
            // jaga konsistensi cache total cart
            $item->cart?->recalcTotals();
        });

        static::deleted(function (CartItem $item) {
            $item->cart?->recalcTotals();
        });
    }

}
