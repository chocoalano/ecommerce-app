<?php

namespace App\Models\CartProduct;

use App\Models\Auth\Customer;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    /** @use HasFactory<\Database\Factories\CartProduct\CartFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'session_id',
        'currency',
        'subtotal_amount',
        'discount_amount',
        'shipping_amount',
        'tax_amount',
        'grand_total',
        'promo_id',
        'voucher_id',
    ];

    protected $casts = [
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'applied_promos' => 'array',
    ];

    /* Relationships */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'cart_items')
            ->withPivot(['qty', 'price', 'row_total', 'product_sku', 'product_name', 'options'])
            ->withTimestamps();
    }

    /* Scopes */
    public function scopeForSession($q, string $sid)
    {
        return $q->where('session_id', $sid);
    }

    public function scopeForCustomer($q, int $cid)
    {
        return $q->where('customer_id', $cid);
    }

    /* Business helpers */

    /** Tambah/merge item (unique: cart_id + product_id) */
    public function addOrIncrementProduct(Product $product, int $qty = 1, ?float $unitPrice = null, array $meta = []): CartItem
    {
        $unitPrice ??= (float) $product->base_price;
        $item = $this->items()->firstOrNew(['product_id' => $product->id]);

        if (! $item->exists) {
            $item->fill([
                'qty' => 0,
                'price' => $unitPrice,
                'currency' => $this->currency ?? 'IDR',
                'product_sku' => $product->sku,
                'product_name' => $product->name,
                'options' => $meta ?: null,
            ]);
        }

        // jika harga berubah, pake harga terbaru utk perhitungan berikutnya
        $item->unit_price = $unitPrice;
        $item->qty += max(1, $qty);
        $item->row_total = $item->qty * $item->unit_price; // asumsi tanpa diskon item
        $item->save();

        $this->recalcTotals();

        return $item;
    }

    /** Hitung ulang subtotal/discount/grand_total berdasarkan item */
    public function recalcTotals(): void
    {
        $subBeforeDiscount = $this->items->sum(fn ($i) => $i->qty * (float) $i->unit_price);
        $rowSum = $this->items->sum(fn ($i) => (float) $i->row_total);

        $this->update([
            'subtotal_amount' => $subBeforeDiscount,
            'discount_amount' => max(0, $subBeforeDiscount - $rowSum),
            'shipping_amount' => $this->shipping_amount ?? 0,
            'tax_amount' => $this->tax_amount ?? 0,
            'grand_total' => $rowSum + (float) ($this->shipping_amount ?? 0) + (float) ($this->tax_amount ?? 0),
            'currency' => $this->currency ?: 'IDR',
        ]);
    }

    /* Calculation Methods */

    /**
     * Get total qty of all items in cart
     */
    public function getTotalqtyAttribute(): int
    {
        return $this->items->sum('qty');
    }

    /**
     * Get total number of unique products in cart
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->count();
    }

    /**
     * Get total weight of all items in cart
     */
    public function getTotalWeightAttribute(): int
    {
        return $this->items->sum(function ($item) {
            return $item->qty * ($item->product->weight_gram ?? 0);
        });
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * Check if cart has items
     */
    public function hasItems(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Get formatted grand total
     */
    public function getFormattedGrandTotalAttribute(): string
    {
        return 'Rp '.number_format((float) $this->grand_total, 0, ',', '.');
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp '.number_format((float) $this->subtotal_amount, 0, ',', '.');
    }

    /**
     * Clear all items from cart
     */
    public function clearItems(): void
    {
        $this->items()->delete();
        $this->recalcTotals();
    }

    /**
     * Remove specific product from cart
     */
    public function removeProduct(Product $product): bool
    {
        $item = $this->items()->where('product_id', $product->id)->first();
        if ($item) {
            $item->delete();

            return true;
        }

        return false;
    }

    /**
     * Update qty for specific product
     */
    public function updateProductqty(Product $product, int $qty): ?CartItem
    {
        $item = $this->items()->where('product_id', $product->id)->first();
        if ($item) {
            if ($qty <= 0) {
                $item->delete();

                return null;
            }

            $item->qty = $qty;
            $item->row_total = $item->qty * $item->unit_price;
            $item->save();

            return $item;
        }

        return null;
    }
}
