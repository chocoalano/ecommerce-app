<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'full_name', 'email', 'password', 'phone', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean',
        'password' => 'hashed',
    ];

    // --- Relationships ---
    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function defaultAddress()
    {
        return $this->hasOne(CustomerAddress::class)->where('is_default', true);
    }

    public function carts()
    {
        return $this->hasMany(\App\Models\CartProduct\Cart::class);
    }

    public function activeCart()
    {
        return $this->hasOne(\App\Models\CartProduct\Cart::class)->latest();
    }

    public function cartItems()
    {
        return $this->hasManyThrough(
            \App\Models\CartProduct\CartItem::class,
            \App\Models\CartProduct\Cart::class
        );
    }

    // --- Cart Helper Methods ---

    /**
     * Get or create active cart for customer
     */
    public function getOrCreateCart(): \App\Models\CartProduct\Cart
    {
        $cart = $this->activeCart;

        if (!$cart) {
            $cart = $this->carts()->create([
                'currency' => 'IDR',
                'subtotal_amount' => 0,
                'discount_amount' => 0,
                'shipping_amount' => 0,
                'tax_amount' => 0,
                'grand_total' => 0,
            ]);
        }

        return $cart;
    }

    /**
     * Get total items in active cart
     */
    public function getCartItemsCountAttribute(): int
    {
        return $this->activeCart?->getTotalQtyAttribute() ?? 0;
    }

    /**
     * Get formatted cart total
     */
    public function getFormattedCartTotalAttribute(): string
    {
        $total = $this->activeCart?->grand_total ?? 0;
        return 'Rp ' . number_format((float) $total, 0, ',', '.');
    }
}
