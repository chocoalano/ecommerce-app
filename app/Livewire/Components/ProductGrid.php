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

    /** Gunakan tema Tailwind Livewire */
    protected $paginationTheme = 'tailwind';

    /** STATE */
    public array $filters = [];
    public string $sort = 'popular';
    public int $perPage = 12;

    /** Dengarkan event dari filter form */
    protected $listeners = ['filtersUpdated' => 'applyFilters'];

    /** Persist di URL agar back/forward tetap konsisten */
    protected $queryString = [
        'sort'    => ['as' => 'sort',     'except' => 'popular'],
        'perPage' => ['as' => 'per_page', 'except' => 12],
        // 'page' dikelola otomatis oleh WithPagination
    ];

    /** Reset page SEBELUM nilai berubah (lebih aman di Livewire v3) */
    public function updatingSort(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        // pastikan integer
        $this->perPage = max(1, (int) $this->perPage);
        $this->resetPage();
    }

    public function updatingFilters(): void
    {
        $this->resetPage();
    }

    /** Dipanggil saat form/filter mengirim event 'filtersUpdated' */
    public function applyFilters(array $filters = []): void
    {
        $this->filters = $filters;
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::query()
            ->select('products.*')
            ->with(['media'])
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
            $cats = (array) $this->filters['categories']; // asumsikan slug
            $query->whereHas('categories', fn(Builder $q) => $q->whereIn('slug', $cats));
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
            $query->where('products.stock', '>', 0);
        }

        if (!empty($this->filters['onSale'])) {
            $query->whereHas('promotions');
        }

        // === Sorting ===
        switch ($this->sort) {
            case 'new':
                $query->orderBy('products.created_at', 'desc');
                break;

            case 'price_asc':
                $query->orderBy('products.base_price', 'asc')->orderBy('products.created_at', 'desc');
                break;

            case 'price_desc':
                $query->orderBy('products.base_price', 'desc')->orderBy('products.created_at', 'desc');
                break;

            case 'popular':
            default:
                $query->orderBy('reviews_count', 'desc')
                      ->orderBy(DB::raw('COALESCE(avg_rating,0)'), 'desc')
                      ->orderBy('products.created_at', 'desc');
                break;
        }

        // Penting: JANGAN pakai view pagination Laravel biasa.
        // Cukup paginate; Livewire akan render link yang benar (wire:click) melalui view 'livewire::tailwind'
        $products = $query->paginate($this->perPage);

        return view('livewire.components.product-grid', [
            'products' => $products,
        ]);
    }
}
