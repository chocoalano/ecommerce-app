<?php

namespace App\Contracts\Repositories;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find product by slug
     */
    public function findBySlug(string $slug): ?Product;

    /**
     * Find product by SKU
     */
    public function findBySku(string $sku): ?Product;

    /**
     * Get active products
     */
    public function getActive(): Collection;

    /**
     * Get products by category
     */
    public function getByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get products by categories
     */
    public function getByCategories(array $categoryIds, int $perPage = 15): LengthAwarePaginator;

    /**
     * Search products
     */
    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get featured products
     */
    public function getFeatured(int $limit = 12): Collection;

    /**
     * Get latest products
     */
    public function getLatest(int $limit = 12): Collection;

    /**
     * Get products with low stock
     */
    public function getLowStock(int $threshold = 10): Collection;

    /**
     * Get products by price range
     */
    public function getByPriceRange(float $minPrice, float $maxPrice, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get related products
     */
    public function getRelated(Product $product, int $limit = 8): Collection;

    /**
     * Get products with promotions
     */
    public function getWithPromotions(): Collection;

    /**
     * Update stock
     */
    public function updateStock(int $id, int $quantity): bool;

    /**
     * Bulk update stock
     */
    public function bulkUpdateStock(array $updates): bool;

    /**
     * Get products with reviews
     */
    public function getWithReviews(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get products by brand
     */
    public function getByBrand(string $brand, int $perPage = 15): LengthAwarePaginator;
}
