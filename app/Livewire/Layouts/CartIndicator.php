<?php

namespace App\Livewire\Layouts;

use App\Models\Cart;
use Livewire\Component;

class CartIndicator extends Component
{
    public int $count = 0;

    protected $listeners = ['cartUpdated' => 'refreshCount'];

    public function mount()
    {
        $this->refreshCount();
    }

    public function refreshCount()
    {
        $this->count = app(Cart::class)->count(); // ambil qty item di cart
    }
    public function render()
    {
        return view('livewire.layouts.cart-indicator');
    }
}
