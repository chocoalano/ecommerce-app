<?php
namespace App\Livewire\Components;

use Livewire\Component;

class HeroSection extends Component
{
    public $heroType;
    public $heroTitle;
    public $heroDesc;
    public $heroTag;
    public $heroImg;
    public $heroLink;
    public $heroMore;
    public $hero;

    public function mount($heroType, $heroTitle, $heroDesc, $heroTag = null, $heroImg, $heroLink, $heroMore, $hero = null)
    {
        $this->heroType  = $heroType;
        $this->heroTitle = $heroTitle;
        $this->heroDesc  = $heroDesc;
        $this->heroTag   = $heroTag;
        $this->heroImg   = $heroImg;
        $this->heroLink  = $heroLink;
        $this->heroMore  = $heroMore;
        $this->hero      = $hero;
    }

    public function render()
    {
        return view('livewire.components.hero-section');
    }
}
