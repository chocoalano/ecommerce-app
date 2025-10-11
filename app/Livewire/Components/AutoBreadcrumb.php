<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Str;

class AutoBreadcrumb extends Component
{
    public array $items = [];
    public bool $showHome = true;
    public string $homeLabel = 'Beranda';
    public string $homeUrl = '/';
    public bool $autoGenerate = true;

    public function mount(
        array $items = [],
        bool $showHome = true,
        string $homeLabel = 'Beranda',
        string $homeUrl = '/',
        bool $autoGenerate = true
    ) {
        $this->showHome = $showHome;
        $this->homeLabel = $homeLabel;
        $this->homeUrl = $homeUrl;
        $this->autoGenerate = $autoGenerate;

        if (!empty($items)) {
            $this->items = $items;
        } elseif ($autoGenerate) {
            $this->generateFromRoute();
        }
    }

    /**
     * Auto-generate breadcrumb from current route
     */
    protected function generateFromRoute(): void
    {
        $route = request()->route();
        if (!$route) return;

        $routeName = $route->getName();
        $parameters = $route->parameters();

        switch (true) {
            case Str::contains($routeName, 'product.'):
                $this->generateProductBreadcrumb($parameters);
                break;

            case Str::contains($routeName, 'category'):
                $this->generateCategoryBreadcrumb($parameters);
                break;

            case Str::contains($routeName, 'search'):
                $this->generateSearchBreadcrumb();
                break;

            case Str::contains($routeName, 'cart'):
                $this->generateCartBreadcrumb($routeName);
                break;

            case Str::contains($routeName, 'user.') || Str::contains($routeName, 'profile'):
                $this->generateUserBreadcrumb($routeName);
                break;

            default:
                $this->generateDefaultBreadcrumb($routeName);
                break;
        }
    }

    /**
     * Generate product breadcrumb
     */
    protected function generateProductBreadcrumb(array $parameters): void
    {
        if (isset($parameters['product'])) {
            $product = $parameters['product'];

            // Add category if exists (assume relationship exists)
            if (method_exists($product, 'categories') && $product->categories()->exists()) {
                $category = $product->categories()->first();
                if ($category) {
                    $this->items[] = [
                        'label' => $category->name,
                        'url' => route('products.index', ['category' => $category->slug]),
                        'params' => [],
                        'is_active' => false
                    ];
                }
            }

            // Add product as final item
            $this->items[] = [
                'label' => $product->name ?? $product->title ?? 'Product',
                'url' => null,
                'params' => [],
                'is_active' => true
            ];
        }
    }

    /**
     * Generate category breadcrumb
     */
    protected function generateCategoryBreadcrumb(array $parameters): void
    {
        if (isset($parameters['category'])) {
            $category = $parameters['category'];

            // Add parent categories if nested
            if (method_exists($category, 'parent') && $category->parent) {
                $this->addParentCategories($category->parent);
            }

            // Add current category
            $this->items[] = [
                'label' => $category->name ?? 'Category',
                'url' => null,
                'params' => [],
                'is_active' => true
            ];
        }
    }

    /**
     * Generate search breadcrumb
     */
    protected function generateSearchBreadcrumb(): void
    {
        $query = request()->get('q', '');
        $label = 'Pencarian';

        if ($query) {
            $label .= ': ' . Str::limit($query, 30);
        }

        $this->items[] = [
            'label' => $label,
            'url' => null,
            'params' => [],
            'is_active' => true
        ];
    }

    /**
     * Generate cart-related breadcrumb
     */
    protected function generateCartBreadcrumb(string $routeName): void
    {
        if (Str::contains($routeName, 'checkout')) {
            $this->items[] = [
                'label' => 'Keranjang',
                'url' => route('cart.index'),
                'params' => [],
                'is_active' => false
            ];

            $this->items[] = [
                'label' => 'Checkout',
                'url' => null,
                'params' => [],
                'is_active' => true
            ];
        } else {
            $this->items[] = [
                'label' => 'Keranjang Belanja',
                'url' => null,
                'params' => [],
                'is_active' => true
            ];
        }
    }

    /**
     * Generate user-related breadcrumb
     */
    protected function generateUserBreadcrumb(string $routeName): void
    {
        $this->items[] = [
            'label' => 'Akun Saya',
            'url' => route('user.profile'),
            'params' => [],
            'is_active' => false
        ];

        if (Str::contains($routeName, 'orders')) {
            $this->items[] = [
                'label' => 'Pesanan',
                'url' => null,
                'params' => [],
                'is_active' => true
            ];
        } elseif (Str::contains($routeName, 'addresses')) {
            $this->items[] = [
                'label' => 'Alamat',
                'url' => null,
                'params' => [],
                'is_active' => true
            ];
        } else {
            $this->items[] = [
                'label' => 'Profil',
                'url' => null,
                'params' => [],
                'is_active' => true
            ];
        }
    }

    /**
     * Generate default breadcrumb from route name
     */
    protected function generateDefaultBreadcrumb(string $routeName): void
    {
        $segments = explode('.', $routeName);
        $label = Str::title(str_replace(['-', '_'], ' ', end($segments)));

        $this->items[] = [
            'label' => $label,
            'url' => null,
            'params' => [],
            'is_active' => true
        ];
    }

    /**
     * Recursively add parent categories
     */
    protected function addParentCategories($category): void
    {
        if ($category->parent) {
            $this->addParentCategories($category->parent);
        }

        $this->items[] = [
            'label' => $category->name,
            'url' => route('products.index', ['category' => $category->slug]),
            'params' => [],
            'is_active' => false
        ];
    }

    public function render()
    {
        return view('livewire.components.breadcrumb');
    }
}
