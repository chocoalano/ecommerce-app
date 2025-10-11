<?php

namespace App\Livewire\Components;

use App\Models\Product\Category;
use Livewire\Component;

class FilterSidebar extends Component
{
    // Filter properties, synced with the query string
    public $q;
    public $categories = [];
    public $minPrice;
    public $maxPrice;
    public $minRating;
    public $inStock;
    public $onSale;
    public $features = [];

    protected $queryString = [
        'q' => ['except' => '', 'as' => 'q'],
        'categories' => ['except' => [], 'as' => 'category'],
        'minPrice' => ['except' => '', 'as' => 'min_price'],
        'maxPrice' => ['except' => '', 'as' => 'max_price'],
        'minRating' => ['except' => '', 'as' => 'min_rating'],
        'inStock' => ['except' => false, 'as' => 'in_stock'],
        'onSale' => ['except' => false, 'as' => 'on_sale'],
        'features' => ['except' => [], 'as' => 'features'],
    ];

    public function updatedQ() { $this->dispatchFilters(); }
    public function updatedCategories() { $this->dispatchFilters(); }
    public function updatedMinPrice() { $this->dispatchFilters(); }
    public function updatedMaxPrice() { $this->dispatchFilters(); }
    public function updatedMinRating() { $this->dispatchFilters(); }
    public function updatedInStock() { $this->dispatchFilters(); }
    public function updatedOnSale() { $this->dispatchFilters(); }
    public function updatedFeatures() { $this->dispatchFilters(); }

    private function dispatchFilters()
    {
        // Dispatch event to notify other components of filter changes
        $this->dispatch('filtersUpdated', $this->getFilters())->to('components.product-grid');
    }

    public function applyFilters()
    {
        // Method untuk trigger filter secara manual jika diperlukan
        $this->dispatchFilters();
    }

    public function getFilters()
    {
        return [
            'q' => $this->q,
            'categories' => $this->categories,
            'minPrice' => $this->minPrice,
            'maxPrice' => $this->maxPrice,
            'minRating' => $this->minRating,
            'inStock' => $this->inStock,
            'onSale' => $this->onSale,
            'features' => $this->features,
        ];
    }

    public function resetFilters()
    {
        $this->reset(['q', 'categories', 'minPrice', 'maxPrice', 'minRating', 'inStock', 'onSale', 'features']);
        $this->dispatch('filtersUpdated', [])->to('components.product-grid');
    }

    public function render()
    {
        $categoryOptions = Category::query()->select('id', 'name', 'slug')->get();
        $featureOptions = ['Fast Charging', 'Bluetooth', 'Water Resistant', 'NFC', 'Wi-Fi'];

        return view('livewire.components.filter-sidebar', [
            'categoryOptions' => $categoryOptions,
            'featureOptions' => $featureOptions,
        ]);
    }
}
