<?php

namespace App\Livewire\Components;

use Livewire\Component;

class ProductCarousel extends Component
{
    public string $title;
    public string $description;
    public array $data = [];
    public function render()
    {
        return view('livewire.components.product-carousel');
    }
}
