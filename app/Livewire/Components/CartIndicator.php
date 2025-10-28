<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CartIndicator extends Component
{
    public int $cartCount = 0;
    public $cartData;

    // Dengarkan event untuk update real-time tanpa polling
    protected $listeners = ['cartUpdated' => 'refreshCart'];

    public function mount(): void
    {
        $this->updateCartCount();
        $this->loadCartData();
    }
    public function refreshCart(){ $this->updateCartCount(); $this->loadCartData(); }

    public function loadCartData(): void
    {
        if (Auth::guard('customer')->check()) {
            $user = Auth::guard('customer')->user();

            // Accessor Laravel: getCartItemsCountAttribute() => $user->cart_items_count
            $this->cartData = $user->cartItems()->with('product.image')->get()->toArray();
        } else {
            $this->cartData = null;
        }
    }

    public function updateCartCount(): void
    {
        if (Auth::guard('customer')->check()) {
            $user = Auth::guard('customer')->user();

            // Accessor Laravel: getCartItemsCountAttribute() => $user->cart_items_count
            $this->cartCount = (int) data_get($user, 'cart_items_count', 0);
        } else {
            // Jika ingin mendukung keranjang tamu via session, pakai baris di bawah:
            // $this->cartCount = collect(session('cart', []))->sum('qty');
            $this->cartCount = 0;
        }
    }

    public function render()
    {
        return view('livewire.components.cart-indicator');
    }
}
