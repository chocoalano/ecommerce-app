<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CartIndicator extends Component
{
    public $cartCount = 0;

    protected $listeners = ['cartUpdated' => 'updateCartCount'];

    public function mount()
    {
        $this->updateCartCount();
    }

    public function updateCartCount()
    {
        if (Auth::guard('customer')->check()) {
            $user = Auth::guard('customer')->user();
            $this->cartCount = $user->getCartItemsCountAttribute() ?? 0;
        } else {
            $this->cartCount = 0;
        }
    }

    public function render()
    {
        return view('livewire.components.cart-indicator');
    }
}
