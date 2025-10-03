<?php

namespace App\Livewire\Components;

use Livewire\Component;

class PromotionHero extends Component
{
    public $title = '2025 AI TVs';
    public $subtitle = 'Explore new AI TVs';
    public $primary = ['label' => 'Lebih detail', 'href' => '#'];
    public $secondary = ['label' => 'Lihat semua', 'href' => '#'];
    public $image = null;
    public $imageAlt = 'Hero image';
    public $containerClass = '';
    public function render()
    {
        return view('livewire.components.promotion-hero');
    }
}
