<?php

namespace App\Livewire\Components;

use App\Models\Product\Category;
use App\Models\Product\Product;
use Livewire\Component;

class FilterSidebar extends Component
{
    // === State filter (default) ===
    public $q = '';
    public array $categories = [];
    public $minPrice = '';
    public $maxPrice = '';
    public $minRating = '';
    public bool $inStock = false;
    public bool $onSale = false;
    public array $features = [];

    /**
     * Sinkron properti <-> query string (key URL di 'as')
     * Gunakan nilai 'except' yang cocok dengan default di atas,
     * supaya param otomatis dihapus saat nilainya kembali default.
     */
    protected $queryString = [
        'q'         => ['except' => '',  'as' => 'q'],
        'categories'=> ['except' => [],  'as' => 'category'],
        'minPrice'  => ['except' => '',  'as' => 'min_price'],
        'maxPrice'  => ['except' => '',  'as' => 'max_price'],
        'minRating' => ['except' => '',  'as' => 'min_rating'],
        'inStock'   => ['except' => false, 'as' => 'in_stock'],
        'onSale'    => ['except' => false, 'as' => 'on_sale'],
        'features'  => ['except' => [],  'as' => 'features'],
    ];

    // =========================
    // Lifecycle & URL Hydration
    // =========================

    public function mount(): void
    {
        // Ambil semua query URL (sudah ter-URL decode)
        $qs = request()->query();

        // Map alias URL -> properti komponen
        $map = [
            'q'          => 'q',
            'category'   => 'categories',
            'categories' => 'categories', // fallback
            'min_price'  => 'minPrice',
            'max_price'  => 'maxPrice',
            'min_rating' => 'minRating',
            'in_stock'   => 'inStock',
            'on_sale'    => 'onSale',
            'features'   => 'features',
        ];

        $seen = false;

        foreach ($map as $urlKey => $prop) {
            if (!array_key_exists($urlKey, $qs)) {
                continue;
            }
            $val = $qs[$urlKey];
            $seen = true;

            switch ($prop) {
                case 'q':
                    $this->q = is_string($val) ? trim($val) : '';
                    break;

                case 'categories':
                    $this->categories = $this->normalizeArrayParam($val, castToInt: true);
                    break;

                case 'features':
                    // fitur pakai slug/string; normalisasi ke lowercase unik
                    $this->features = array_values(array_unique(
                        array_filter(array_map(fn($v) => strtolower(trim((string)$v)), $this->normalizeArrayParam($val)))
                    ));
                    break;

                case 'minPrice':
                case 'maxPrice':
                case 'minRating':
                    $num = $this->parseNumeric($val);
                    // simpan sebagai string agar cocok dengan 'except' => '' (hapus dari URL saat reset)
                    $this->{$prop} = $num === null ? '' : (string)$num;
                    break;

                case 'inStock':
                case 'onSale':
                    $this->{$prop} = $this->parseBool($val);
                    break;
            }
        }

        // Jika URL memuat filter apa pun, broadcast ke grid sekali di awal
        if ($seen) {
            $this->dispatchFilters();
        }
    }

    /**
     * Satu hook untuk semua perubahan properti.
     * Menggantikan banyak updatedX().
     */
    public function updated($name, $value): void
    {
        // Normalisasi ringan agar URL bersih saat user mengetik kosong
        if (in_array($name, ['minPrice', 'maxPrice', 'minRating'], true)) {
            $this->{$name} = $value === null ? '' : (string)$value;
        }

        $this->dispatchFilters();
    }

    // ======================
    // Actions / Event Bridge
    // ======================

    private function dispatchFilters(): void
    {
        // Kirim event ke komponen grid (ubah target sesuai alias komponen Anda)
        $this->dispatch('filtersUpdated', $this->getFilters())
            ->to('components.product-grid');
    }

    public function applyFilters(): void
    {
        // Jika Anda punya tombol "Apply", ini memaksa broadcast
        $this->dispatchFilters();
    }

    public function resetFilters(): void
    {
        // Kembalikan ke nilai yang cocok dgn 'except' agar param URL hilang otomatis
        $this->q         = '';
        $this->categories= [];
        $this->minPrice  = '';
        $this->maxPrice  = '';
        $this->minRating = '';
        $this->inStock   = false;
        $this->onSale    = false;
        $this->features  = [];

        $this->dispatch('filtersUpdated', [])->to('components.product-grid');
    }

    public function getFilters(): array
    {
        return [
            'search'     => $this->q ?: null,
            'categories' => $this->categories, // array<int>
            'min_price'  => $this->minPrice !== ''  ? (float)$this->minPrice : null,
            'max_price'  => $this->maxPrice !== ''  ? (float)$this->maxPrice : null,
            'min_rating' => $this->minRating !== '' ? (float)$this->minRating : null,
            'in_stock'   => (bool)$this->inStock,
            'on_sale'    => (bool)$this->onSale,
            'features'   => $this->features,  // array<string>
        ];
    }

    // ==========
    // Utilities
    // ==========

    /**
     * Terima nilai array dari URL baik dalam bentuk:
     * - "1,2,3"
     * - ["1","2","3"] (category[]=1&category[]=2)
     * - campuran string/angka
     */
    private function normalizeArrayParam($value, bool $castToInt = false): array
    {
        if (is_string($value)) {
            $value = array_filter(array_map('trim', explode(',', $value)), fn($v) => $v !== '');
        } elseif (is_array($value)) {
            // flaten satu level
            $flat = [];
            array_walk_recursive($value, function ($v) use (&$flat) {
                $flat[] = $v;
            });
            $value = array_filter(array_map(fn($v) => is_string($v) ? trim($v) : $v, $flat), fn($v) => $v !== '' && $v !== null);
        } else {
            return [];
        }

        if ($castToInt) {
            $value = array_values(array_unique(array_map(fn($v) => (int)$v, array_filter($value, fn($v) => is_numeric($v)))));
        } else {
            $value = array_values(array_unique(array_map(fn($v) => (string)$v, $value)));
        }

        return $value;
    }

    private function parseBool($value): bool
    {
        if (is_bool($value)) return $value;

        $t = strtolower((string)$value);
        return in_array($t, ['1','true','yes','on'], true);
    }

    private function parseNumeric($value): ?float
    {
        if (is_numeric($value)) return (float)$value;
        if (is_string($value)) {
            $v = trim($value);
            if ($v === '') return null;
            // dukung format "10.000" -> 10000
            $v = str_replace(['.', ','], ['', '.'], $v); // sederhana: 10.000,50 -> 10000.50
            return is_numeric($v) ? (float)$v : null;
        }
        return null;
    }

    // ======
    // Render
    // ======

    public function render()
    {
        $categoryOptions = Category::query()->select('id', 'name', 'slug')->orderBy('name')->get();
        $featureOptions  = Product::select('brand')->distinct()->orderBy('brand')->pluck('brand')->filter()->values()->toArray();

        return view('livewire.components.filter-sidebar', [
            'categoryOptions' => $categoryOptions,
            'featureOptions'  => $featureOptions,
        ]);
    }
}
