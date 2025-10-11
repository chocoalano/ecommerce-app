<?php

namespace App\Livewire\Components;

use App\Models\Product\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductGrid extends Component
{
    use WithPagination;

    public $filters = [];
    public $sort = 'popular';
    public $perPage = 12;

    protected $listeners = ['filtersUpdated' => 'applyFilters'];

    public function applyFilters($filters)
    {
        $this->filters = $filters;
        $this->resetPage();
    }

    public function updatedSort()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::query()->with(['media', 'reviews']);

        // Apply filters
        if (!empty($this->filters['q'])) {
            $query->where('name', 'like', '%' . $this->filters['q'] . '%');
        }
        if (!empty($this->filters['categories'])) {
            $query->whereHas('categories', function ($q) {
                $q->whereIn('slug', $this->filters['categories']);
            });
        }
        if (!empty($this->filters['minPrice'])) {
            $query->where('base_price', '>=', $this->filters['minPrice']);
        }
        if (!empty($this->filters['maxPrice'])) {
            $query->where('base_price', '<=', $this->filters['maxPrice']);
        }
        if (!empty($this->filters['minRating'])) {
            $query->withAvg('reviews as avg_rating', 'rating')
                  ->having('avg_rating', '>=', $this->filters['minRating']);
        }
        if (!empty($this->filters['inStock'])) {
            $query->where('stock', '>', 0);
        }
        if (!empty($this->filters['onSale'])) {
            $query->whereHas('promotions');
        }

        // Apply sorting
        switch ($this->sort) {
            case 'new':
                $query->latest();
                break;
            case 'price_asc':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('base_price', 'desc');
                break;
            case 'popular':
            default:
                // Add your logic for popular sorting, e.g., by sales or views
                $query->inRandomOrder(); // Placeholder
                break;
        }

        $products = $query->paginate($this->perPage);

        return view('livewire.components.product-grid', [
            'products' => $products,
        ]);
    }
}
