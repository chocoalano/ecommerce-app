<?php

namespace App\Services\Product;

use App\Contracts\Services\ProductServiceInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService implements ProductServiceInterface
{
    protected ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get all products with filters
     */
    public function getAllProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $filters = array_change_key_case($filters, CASE_LOWER);

        $query = Product::query()
            ->select('products.*')
            ->where('products.is_active', true)
            ->with([
                'categories:id,name',   // pilih kolom secukupnya
                'media',
                'primaryMedia',
            ]);

        // --- Aggregasi rating: avg_rating & reviews_count (hanya yang approved) ---
        $ratingAgg = DB::table('product_reviews as pr')
            ->selectRaw('pr.product_id, AVG(pr.rating) AS avg_rating, COUNT(*) AS reviews_count')
            ->where('pr.is_approved', true)
            ->groupBy('pr.product_id');

        $query->leftJoinSub($ratingAgg, 'r', function ($join) {
            $join->on('r.product_id', '=', 'products.id');
        });

        // --- Search (full-text bila ada, fallback LIKE) ---
        if ($search = trim($filters['search'] ?? '')) {
            $query->where(function (Builder $q) use ($search) {
                try {
                    // MySQL 5.7+/8 dengan index fulltext di name & description
                    $q->whereFullText(['products.name', 'products.description'], $search);
                } catch (\Throwable $e) {
                    $q->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.description', 'like', "%{$search}%");
                }
            });
        }

        // --- Price range ---
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $query->where('products.base_price', '>=', (float) $filters['min_price']);
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $query->where('products.base_price', '<=', (float) $filters['max_price']);
        }

        // --- Min rating ---
        if (isset($filters['min_rating']) && is_numeric($filters['min_rating']) && (float)$filters['min_rating'] > 0) {
            // null avg_rating (produk tanpa review) otomatis terfilter keluar
            $query->where('r.avg_rating', '>=', (float) $filters['min_rating']);
        }

        // --- Categories (array of IDs) ---
        if (!empty($filters['categories']) && is_array($filters['categories'])) {
            $ids = array_values(array_filter($filters['categories'], fn($v) => is_numeric($v)));
            if ($ids) {
                $query->whereHas('categories', function (Builder $q) use ($ids) {
                    $q->whereIn('categories.id', $ids);
                });
            }
        }

        // --- In stock ---
        if (!empty($filters['in_stock'])) {
            $query->where('products.stock_quantity', '>', 0);
        }

        // --- On sale ---
        if (!empty($filters['on_sale'])) {
            // Sesuaikan dengan skema Anda (is_on_sale/discount_price)
            $query->where(function (Builder $q) {
                $q->where('products.is_on_sale', true)
                ->orWhereNotNull('products.discount_price');
            });
        }

        // --- Sorting ---
        $sortBy       = $filters['sort_by'] ?? 'created_at';
        $sortDirInput = strtolower($filters['sort_direction'] ?? 'desc');
        $sortDir      = in_array($sortDirInput, ['asc', 'desc'], true) ? $sortDirInput : 'desc';

        $sortMap = [
            'created_at' => 'products.created_at',
            'price'      => 'products.base_price',
            'name'       => 'products.name',
            'rating'     => DB::raw('COALESCE(r.avg_rating, 0)'),
        ];
        if (!array_key_exists($sortBy, $sortMap)) {
            $sortBy = 'created_at';
        }

        if ($sortBy === 'rating') {
            $query->orderBy($sortMap['rating'], $sortDir)
                ->orderBy(DB::raw('COALESCE(r.reviews_count, 0)'), $sortDir)
                ->orderBy('products.created_at', 'desc');
        } else {
            $query->orderBy($sortMap[$sortBy], $sortDir)
                ->orderBy('products.created_at', 'desc');
        }

        // --- Paginate ---
        return $query->paginate($perPage)->appends($filters);
    }

    /**
     * Get product by SKU with full details
     */
    public function getProductBySku(string $sku): ?Product
    {
        return $this->productRepository
                ->with([
                    'categories',
                    'media',
                    'primaryMedia',
                    'reviews.customer',
                    'promotions' => function ($query) {
                        $query->where('is_active', true);
                    }
                ])
                ->findBySku($sku);
    }

    /**
     * Search products
     */
    public function searchProducts(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->search($query, $filters, $perPage);
    }

    /**
     * Get featured products
     */
    public function getFeaturedProducts(int $limit = 12): Collection
    {
        $cacheKey = "products.featured.{$limit}";

        return Cache::remember($cacheKey, 1800, function () use ($limit) {
            return $this->productRepository->getFeatured($limit);
        });
    }

    /**
     * Get latest products
     */
    public function getLatestProducts(int $limit = 12): Collection
    {
        $cacheKey = "products.latest.{$limit}";

        return Cache::remember($cacheKey, 900, function () use ($limit) {
            return $this->productRepository->getLatest($limit);
        });
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory(int $categoryId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        if (!empty($filters['search'])) {
            $filters['categories'] = [$categoryId];
            return $this->productRepository->search($filters['search'], $filters, $perPage);
        }

        return $this->productRepository->getByCategory($categoryId, $perPage);
    }

    /**
     * Get related products
     */
    public function getRelatedProducts(Product $product, int $limit = 8): Collection
    {
        return $this->productRepository->getRelated($product, $limit);
    }

    /**
     * Create new product
     */
    public function createProduct(array $data): Product
    {
        DB::beginTransaction();
        try {
            $product = $this->productRepository->create($data);

            // Handle categories
            if (!empty($data['categories'])) {
                $product->categories()->sync($data['categories']);
            }

            // Handle media
            if (!empty($data['media'])) {
                $this->attachMedia($product, $data['media']);
            }

            // Clear cache
            $this->clearProductCache();

            DB::commit();
            return $product->load(['categories', 'media']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create product', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update product
     */
    public function updateProduct(int $id, array $data): Product
    {
        DB::beginTransaction();
        try {
            $this->productRepository->update($id, $data);
            $product = $this->productRepository->findOrFail($id);

            // Handle categories
            if (isset($data['categories'])) {
                $product->categories()->sync($data['categories']);
            }

            // Handle media
            if (isset($data['media'])) {
                $this->attachMedia($product, $data['media']);
            }

            // Clear cache
            $this->clearProductCache($product->slug);

            DB::commit();
            return $product->load(['categories', 'media']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update product', [
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete product
     */
    public function deleteProduct(int $id): bool
    {
        DB::beginTransaction();
        try {
            $product = $this->productRepository->findOrFail($id);

            // Delete related data
            $product->categories()->detach();
            $product->media()->delete();
            $product->promotions()->detach();

            $result = $this->productRepository->delete($id);

            // Clear cache
            $this->clearProductCache($product->slug);

            DB::commit();
            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete product', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update product stock
     */
    public function updateStock(int $productId, int $quantity, string $reason = ''): bool
    {
        try {
            $product = $this->productRepository->findOrFail($productId);
            $oldStock = $product->stock;

            $result = $this->productRepository->updateStock($productId, $quantity);

            if ($result) {
                // Log stock movement
                Log::info('Stock updated', [
                    'product_id' => $productId,
                    'old_stock' => $oldStock,
                    'new_stock' => $quantity,
                    'reason' => $reason
                ]);

                // Clear cache
                $this->clearProductCache($product->slug);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to update stock', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check product availability
     */
    public function checkAvailability(int $productId, int $quantity = 1): bool
    {
        $product = $this->productRepository->find($productId);

        if (!$product || !$product->is_active) {
            return false;
        }

        return $product->stock >= $quantity;
    }

    /**
     * Get product recommendations
     */
    public function getRecommendations(array $criteria = [], int $limit = 12): Collection
    {
        $cacheKey = 'products.recommendations.' . md5(serialize($criteria)) . ".{$limit}";

        return Cache::remember($cacheKey, 1800, function () use ($criteria, $limit) {
            // Basic recommendation logic - can be enhanced with ML
            if (!empty($criteria['category_ids'])) {
                return $this->productRepository->getByCategories($criteria['category_ids'], $limit);
            }

            return $this->productRepository->getFeatured($limit);
        });
    }

    /**
     * Calculate product price with promotions
     */
    public function calculatePrice(Product $product, int $quantity = 1): array
    {
        $basePrice = $product->base_price;
        $totalBasePrice = $basePrice * $quantity;
        $finalPrice = $totalBasePrice;
        $discount = 0;
        $appliedPromotions = [];

        // Apply promotions
        foreach ($product->promotions as $promotion) {
            if ($promotion->pivot->min_qty <= $quantity) {
                if ($promotion->pivot->discount_percent > 0) {
                    $promotionDiscount = $totalBasePrice * ($promotion->pivot->discount_percent / 100);
                } else {
                    $promotionDiscount = $promotion->pivot->discount_value * $quantity;
                }

                if ($promotionDiscount > $discount) {
                    $discount = $promotionDiscount;
                    $appliedPromotions = [$promotion];
                }
            }
        }

        $finalPrice = max(0, $totalBasePrice - $discount);

        return [
            'base_price' => $basePrice,
            'total_base_price' => $totalBasePrice,
            'discount' => $discount,
            'final_price' => $finalPrice,
            'savings' => $discount,
            'applied_promotions' => $appliedPromotions,
            'quantity' => $quantity
        ];
    }

    /**
     * Get product statistics
     */
    public function getProductStatistics(int $productId): array
    {
        $cacheKey = "product.stats.{$productId}";

        return Cache::remember($cacheKey, 3600, function () use ($productId) {
            $product = $this->productRepository
                ->with(['reviews', 'orders'])
                ->findOrFail($productId);

            return [
                'total_reviews' => $product->reviews->count(),
                'average_rating' => $product->reviews->avg('rating'),
                'total_sales' => $product->orders->sum('quantity'),
                'revenue' => $product->orders->sum('total_price'),
                'stock_level' => $product->stock,
                'view_count' => $product->view_count ?? 0
            ];
        });
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(array $productIds, bool $status): bool
    {
        try {
            DB::beginTransaction();

            foreach ($productIds as $id) {
                $this->productRepository->update($id, ['is_active' => $status]);
            }

            $this->clearProductCache();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to bulk update status', [
                'product_ids' => $productIds,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Bulk update prices
     */
    public function bulkUpdatePrices(array $updates): bool
    {
        try {
            DB::beginTransaction();

            foreach ($updates as $update) {
                $this->productRepository->update($update['id'], [
                    'base_price' => $update['price']
                ]);
            }

            $this->clearProductCache();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to bulk update prices', [
                'updates' => $updates,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Bulk update stock
     */
    public function bulkUpdateStock(array $updates): bool
    {
        return $this->productRepository->bulkUpdateStock($updates);
    }

    /**
     * Attach media to product
     */
    protected function attachMedia(Product $product, array $media): void
    {
        // Implementation depends on your media handling logic
        foreach ($media as $index => $mediaData) {
            $product->media()->create([
                'url' => $mediaData['url'],
                'alt_text' => $mediaData['alt_text'] ?? '',
                'sort_order' => $index,
                'is_primary' => $index === 0
            ]);
        }
    }

    /**
     * Clear product cache
     */
    protected function clearProductCache(?string $slug = null): void
    {
        $patterns = [
            'products.featured.*',
            'products.latest.*',
            'products.recommendations.*',
        ];

        if ($slug) {
            $patterns[] = "product.slug.{$slug}";
        }

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }

        // Clear cache tags if using tagged cache
        if (method_exists(Cache::class, 'tags')) {
            Cache::tags(['products'])->flush();
        }
    }
}
