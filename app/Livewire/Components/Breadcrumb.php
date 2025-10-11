<?php

namespace App\Livewire\Components;

use Livewire\Component;

class Breadcrumb extends Component
{
    public array $items = [];
    public bool $showHome = true;
    public string $homeLabel = 'Beranda';
    public string $homeUrl = '/';

    public function mount(
        array $items = [],
        bool $showHome = true,
        string $homeLabel = 'Beranda',
        string $homeUrl = '/'
    ) {
        $this->items = $items;
        $this->showHome = $showHome;
        $this->homeLabel = $homeLabel;
        $this->homeUrl = $homeUrl;
    }

    /**
     * Add breadcrumb item
     */
    public function addItem(string $label, ?string $url = null, array $params = []): void
    {
        $this->items[] = [
            'label' => $label,
            'url' => $url,
            'params' => $params,
            'is_active' => false
        ];
    }

    /**
     * Add category breadcrumb
     */
    public function addCategory($category): void
    {
        if ($category) {
            $this->addItem(
                $category->name,
                route('products.index', ['category' => $category->slug])
            );
        }
    }

    /**
     * Add product breadcrumb (final item, no link)
     */
    public function addProduct($product): void
    {
        if ($product) {
            $this->items[] = [
                'label' => $product->name,
                'url' => null,
                'params' => [],
                'is_active' => true
            ];
        }
    }

    /**
     * Set final active item
     */
    public function setActiveItem(string $label): void
    {
        $this->items[] = [
            'label' => $label,
            'url' => null,
            'params' => [],
            'is_active' => true
        ];
    }

    /**
     * Build breadcrumb from route parameters
     */
    public function buildFromRoute(): void
    {
        $routeName = request()->route()->getName();
        $parameters = request()->route()->parameters();

        switch ($routeName) {
            case 'product.detail':
                $this->buildProductBreadcrumb($parameters);
                break;
            case 'category':
                $this->buildCategoryBreadcrumb($parameters);
                break;
            case 'search':
                $this->addItem('Pencarian', null);
                break;
            default:
                // Custom breadcrumb handling
                break;
        }
    }

    /**
     * Build product detail breadcrumb
     */
    private function buildProductBreadcrumb(array $parameters): void
    {
        // Assume we have product model with category relationship
        if (isset($parameters['product'])) {
            $product = $parameters['product'];

            // Add category if exists
            if (isset($product->primaryCategory)) {
                $this->addCategory($product->primaryCategory);
            }

            // Add product name as final item
            $this->addProduct($product);
        }
    }

    /**
     * Build category breadcrumb
     */
    private function buildCategoryBreadcrumb(array $parameters): void
    {
        if (isset($parameters['category'])) {
            $category = $parameters['category'];

            // Add parent categories if needed (for nested categories)
            if (method_exists($category, 'getParents')) {
                foreach ($category->getParents() as $parent) {
                    $this->addCategory($parent);
                }
            }

            // Add current category as active
            $this->setActiveItem($category->name);
        }
    }

    public function render()
    {
        return view('livewire.components.breadcrumb');
    }
}
