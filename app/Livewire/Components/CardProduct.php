<?php

namespace App\Livewire\Components;

use Livewire\Component;

class CardProduct extends Component
{
    public int $id;
    public string $title;
    public string $image;
    public string $price;
    public ?array $data = [];
    public function render()
    {
        return view('livewire.components.card-product');
    }
}
