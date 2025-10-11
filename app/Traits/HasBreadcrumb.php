<?php

namespace App\Traits;

trait HasBreadcrumb
{
    /**
     * Build breadcrumb for product detail page
     */
    protected function buildProductBreadcrumb($product, $primaryCategory = null): array
    {
        $breadcrumbItems = [];

        // Add category if exists
        if ($primaryCategory) {
            $breadcrumbItems[] = [
                'label' => $primaryCategory->name,
                'url' => route('products.index', ['category' => $primaryCategory->slug]),
                'params' => [],
                'is_active' => false
            ];
        }

        // Add product as final active item
        $breadcrumbItems[] = [
            'label' => $product->name,
            'url' => null,
            'params' => [],
            'is_active' => true
        ];

        return $breadcrumbItems;
    }

    /**
     * Build breadcrumb for category page
     */
    protected function buildCategoryBreadcrumb($category): array
    {
        $breadcrumbItems = [];

        // Add parent categories if nested
        if (method_exists($category, 'getParents')) {
            foreach ($category->getParents() as $parent) {
                $breadcrumbItems[] = [
                    'label' => $parent->name,
                    'url' => route('products.index', ['category' => $parent->slug]),
                    'params' => [],
                    'is_active' => false
                ];
            }
        }

        // Add current category as active
        $breadcrumbItems[] = [
            'label' => $category->name,
            'url' => null,
            'params' => [],
            'is_active' => true
        ];

        return $breadcrumbItems;
    }

    /**
     * Build simple breadcrumb
     */
    protected function buildSimpleBreadcrumb(string $label, ?string $url = null): array
    {
        return [
            [
                'label' => $label,
                'url' => $url,
                'params' => [],
                'is_active' => is_null($url)
            ]
        ];
    }

    /**
     * Build search breadcrumb
     */
    protected function buildSearchBreadcrumb(string $query = ''): array
    {
        $label = 'Pencarian';
        if ($query) {
            $label .= ': ' . $query;
        }

        return $this->buildSimpleBreadcrumb($label);
    }
}
