<?php

namespace App\Livewire\Layouts;

use App\Models\Product;
use Livewire\Component;

class SearchBox extends Component
{
    public string $q = '';
    public $results = [];

    public function updatedQ()
    {
        $q = trim($this->q);
        $this->results = $q === ''
            ? []
            : Product::query()
                ->select('id','name','slug','price')
                ->where('is_active', true)
                ->where('name', 'like', "%{$q}%")
                ->limit(6)->get();
    }
    public function render()
    {
        return view('livewire.layouts.search-box');
    }
}
