<?php

namespace App\Livewire\Components;

use Livewire\Component;

class LandingCategory extends Component
{
    public $category = [];
    public function render()
    {
        return view('livewire.components.landing-category');
    }
}
