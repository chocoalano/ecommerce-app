<?php

namespace App\Contracts\Services;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductServiceInterface
{
    /**
     * Get all products with filters
     */
    public function getAllProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get product by SKU with full details
     */
    public function getProductBySku(string $sku): ?Product;

    /**
     * Search products
     */
    public function searchProducts(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get featured products
     */
    public function getFeaturedProducts(int $limit = 12): Collection;

    /**
     * Get latest products
     */
    public function getLatestProducts(int $limit = 12): Collection;

    /**
     * Get products by category
     */
    public function getProductsByCategory(int $categoryId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get related products
     */
    public function getRelatedProducts(Product $product, int $limit = 8): Collection;

    /**
     * Create new product
     */
    public function createProduct(array $data): Product;

    /**
     * Update product
     */
    public function updateProduct(int $id, array $data): Product;

    /**
     * Delete product
     */
    public function deleteProduct(int $id): bool;

    /**
     * Update product stock
     */
    public function updateStock(int $productId, int $quantity, string $reason = ''): bool;

    /**
     * Check product availability
     */
    public function checkAvailability(int $productId, int $quantity = 1): bool;

    /**
     * Get product recommendations
     */
    public function getRecommendations(array $criteria = [], int $limit = 12): Collection;

    /**
     * Calculate product price with promotions
     */
    public function calculatePrice(Product $product, int $quantity = 1): array;

    /**
     * Get product statistics
     */
    public function getProductStatistics(int $productId): array;

    /**
     * Bulk operations
     */
    public function bulkUpdateStatus(array $productIds, bool $status): bool;
    public function bulkUpdatePrices(array $updates): bool;
    public function bulkUpdateStock(array $updates): bool;
}
