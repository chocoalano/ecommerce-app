<?php

namespace App\Livewire\Components;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ProductGrid extends Component
{
    use WithPagination;

    public array $filters = [];
    public string $sort = 'popular';
    public int $perPage = 12;

    protected $paginationTheme = 'tailwind';

    protected $listeners = ['filtersUpdated' => 'applyFilters'];

    // Persist ke URL
    protected $queryString = [
        'sort'    => ['as' => 'sort',     'except' => 'popular'],
        'perPage' => ['as' => 'per_page', 'except' => 12],
    ];

    public function applyFilters($filters): void
    {
        $this->filters = is_array($filters) ? $filters : [];
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        // Cast & guard
        $this->perPage = max(1, (int) $value);
        $this->resetPage();

        // Optional: beri tahu komponen lain / UI
        $this->dispatch('perPageUpdated', perPage: $this->perPage);
    }

    public function render()
    {
        $query = Product::query()
            ->select('products.*')
            ->with(['media']) // ambil yang perlu
            // hitung avg_rating & reviews_count (approved only) untuk display & sorting
            ->withAvg(['reviews as avg_rating' => fn($q) => $q->where('is_approved', true)], 'rating')
            ->withCount(['reviews as reviews_count' => fn($q) => $q->where('is_approved', true)]);

        // === Filters ===
        if (!empty($this->filters['q'])) {
            $search = trim((string) $this->filters['q']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('products.description', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['categories'])) {
            // Anda tadi pakai slug. Jika sebenarnya ID, ganti ke categories.id
            $cats = (array) $this->filters['categories'];
            $query->whereHas('categories', function (Builder $q) use ($cats) {
                $q->whereIn('slug', $cats);
            });
        }

        if (!empty($this->filters['minPrice'])) {
            $query->where('products.base_price', '>=', (float) $this->filters['minPrice']);
        }
        if (!empty($this->filters['maxPrice'])) {
            $query->where('products.base_price', '<=', (float) $this->filters['maxPrice']);
        }

        if (!empty($this->filters['minRating'])) {
            $query->having('avg_rating', '>=', (float) $this->filters['minRating']);
        }

        if (!empty($this->filters['inStock'])) {
            // sesuaikan nama kolom stok Anda
            $query->where('products.stock', '>', 0);
        }

        if (!empty($this->filters['onSale'])) {
            // sesuaikan relasi/polar Anda
            $query->whereHas('promotions');
        }

        // === Sorting ===
        switch ($this->sort) {
            case 'new':
                $query->orderBy('products.created_at', 'desc');
                break;

            case 'price_asc':
                $query->orderBy('products.base_price', 'asc')
                      ->orderBy('products.created_at', 'desc');
                break;

            case 'price_desc':
                $query->orderBy('products.base_price', 'desc')
                      ->orderBy('products.created_at', 'desc');
                break;

            case 'popular':
            default:
                // Lebih stabil daripada inRandomOrder
                $query->orderBy('reviews_count', 'desc')
                      ->orderBy(DB::raw('COALESCE(avg_rating,0)'), 'desc')
                      ->orderBy('products.created_at', 'desc');
                break;
        }

        // Paginate + pertahankan query string (sort/per_page dsb.)
        $products = $query->paginate($this->perPage)->withQueryString();

        return view('livewire.components.product-grid', [
            'products' => $products,
        ]);
    }
}
