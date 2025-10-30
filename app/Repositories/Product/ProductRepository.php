<?php

namespace App\Repositories\Product;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product\Product;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    /**
     * Get fresh model instance
     */
    public function getModel(): Model
    {
        return new Product();
    }

    /**
     * Find product by slug
     */
    public function findBySlug(string $slug): ?Product
    {
        return $this->findBy('slug', $slug);
    }

    /**
     * Find product by SKU
     */
    public function findBySku(string $sku): ?Product
    {
        return $this->findBy('sku', $sku);
    }

    /**
     * Get active products
     */
    public function getActive(): Collection
    {
        return $this->where('is_active', true)->all();
    }

    /**
     * Get products by category
     */
    public function getByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query
            ->whereHas('categories', function ($query) use ($categoryId) {
                $query->where('categories.id', $categoryId);
            })
            ->where('is_active', true)
            ->with(['categories', 'media', 'primaryMedia'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get products by categories
     */
    public function getByCategories(array $categoryIds, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->where('is_active', true)
            ->with(['categories', 'media', 'primaryMedia'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get products by categories
     */
    public function getByRating(array $ratings, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query
            ->whereHas('reviews', function ($query) use ($ratings) {
                $query->whereIn('rating', $ratings);
            })
            ->where('is_active', true)
            ->with(['categories', 'media', 'primaryMedia'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search products
     */
    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $builder = $this->query
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('short_desc', 'LIKE', "%{$query}%")
                  ->orWhere('long_desc', 'LIKE', "%{$query}%")
                  ->orWhere('brand', 'LIKE', "%{$query}%")
                  ->orWhere('sku', 'LIKE', "%{$query}%");
            })
            ->with(['categories', 'media', 'primaryMedia']);

        // Apply filters
        if (!empty($filters['categories'])) {
            $builder->whereHas('categories', function ($q) use ($filters) {
                $q->whereIn('categories.id', (array) $filters['categories']);
            });
        }

        if (!empty($filters['min_price'])) {
            $builder->where('base_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $builder->where('base_price', '<=', $filters['max_price']);
        }

        if (!empty($filters['brand'])) {
            $builder->whereIn('brand', (array) $filters['brand']);
        }

        if (!empty($filters['in_stock'])) {
            $builder->where('stock', '>', 0);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $builder->orderBy($sortBy, $sortDirection);

        $result = $builder->paginate($perPage);
        $this->resetQuery();

        return $result;
    }

    /**
     * Get featured products
     */
    public function getFeatured(int $limit = 12): Collection
    {
        return $this->query
            ->where('is_active', true)
            ->whereHas('promotions')
            ->with(['categories', 'media', 'primaryMedia', 'promotions'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get latest products
     */
    public function getLatest(int $limit = 12): Collection
    {
        return $this->where('is_active', true)
            ->with(['categories', 'media', 'primaryMedia'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->all();
    }

    /**
     * Get products with low stock
     */
    public function getLowStock(int $threshold = 10): Collection
    {
        return $this->query
            ->where('is_active', true)
            ->where('stock', '<=', $threshold)
            ->where('stock', '>', 0)
            ->with(['categories', 'media'])
            ->orderBy('stock', 'asc')
            ->get();
    }

    /**
     * Get products by price range
     */
    public function getByPriceRange(float $minPrice, float $maxPrice, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query
            ->where('is_active', true)
            ->whereBetween('base_price', [$minPrice, $maxPrice])
            ->with(['categories', 'media', 'primaryMedia'])
            ->orderBy('base_price', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get related products
     */
    public function getRelated(Product $product, int $limit = 8): Collection
    {
        $categoryIds = $product->categories->pluck('id')->toArray();
        $product = $this->query
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->when(!empty($categoryIds), function ($query) use ($categoryIds) {
                $query->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });
            })
            ->with(['categories', 'media', 'primaryMedia'])
            ->inRandomOrder()
            ->limit($limit)
            ->get();
        return $product;
    }

    /**
     * Get products with promotions
     */
    public function getWithPromotions(): Collection
    {
        return $this->query
            ->where('is_active', true)
            ->whereHas('promotions', function ($query) {
                $query->where('is_active', true)
                      ->where('start_at', '<=', now())
                      ->where('end_at', '>=', now());
            })
            ->with(['categories', 'media', 'primaryMedia', 'promotions'])
            ->get();
    }

    /**
     * Update stock
     */
    public function updateStock(int $id, int $quantity): bool
    {
        return $this->update($id, ['stock' => $quantity]);
    }

    /**
     * Bulk update stock
     */
    public function bulkUpdateStock(array $updates): bool
    {
        try {
            foreach ($updates as $update) {
                $this->updateStock($update['id'], $update['stock']);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get products with reviews
     */
    public function getWithReviews(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query
            ->where('is_active', true)
            ->whereHas('reviews', function ($query) {
                $query->where('is_approved', true);
            })
            ->withCount(['reviews as reviews_count' => function ($query) {
                $query->where('is_approved', true);
            }])
            ->withAvg(['reviews as avg_rating' => function ($query) {
                $query->where('is_approved', true);
            }], 'rating')
            ->with(['categories', 'media', 'primaryMedia'])
            ->orderBy('reviews_count', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get products by brand
     */
    public function getByBrand(string $brand, int $perPage = 15): LengthAwarePaginator
    {
        return $this->where('brand', $brand)
            ->where('is_active', true)
            ->with(['categories', 'media', 'primaryMedia'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
