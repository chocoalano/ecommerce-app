<?php

namespace App\Services\Product;

use App\Contracts\Services\CategoryServiceInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Product\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryService implements CategoryServiceInterface
{
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get all active categories
     */
    public function getAllCategories(): Collection
    {
        return Cache::remember('categories.all', 3600, function () {
            return $this->categoryRepository->getActive();
        });
    }

    /**
     * Get category by slug
     */
    public function getCategoryBySlug(string $slug): ?Category
    {
        $cacheKey = "category.slug.{$slug}";

        return Cache::remember($cacheKey, 3600, function () use ($slug) {
            return $this->categoryRepository
                ->with(['products' => function ($query) {
                    $query->where('is_active', true)->limit(12);
                }, 'children', 'parent'])
                ->findBySlug($slug);
        });
    }

    /**
     * Get category tree
     */
    public function getCategoryTree(): Collection
    {
        return Cache::remember('categories.tree', 3600, function () {
            return $this->categoryRepository->getTree();
        });
    }

    /**
     * Get categories with products count
     */
    public function getCategoriesWithProductsCount(): Collection
    {
        return Cache::remember('categories.with_products_count', 1800, function () {
            return $this->categoryRepository->getWithProductsCount();
        });
    }

    /**
     * Get parent categories
     */
    public function getParentCategories(): Collection
    {
        return Cache::remember('categories.parents', 3600, function () {
            return $this->categoryRepository->getParents();
        });
    }

    /**
     * Get child categories
     */
    public function getChildCategories(int $parentId): Collection
    {
        $cacheKey = "categories.children.{$parentId}";

        return Cache::remember($cacheKey, 3600, function () use ($parentId) {
            return $this->categoryRepository->getChildren($parentId);
        });
    }

    /**
     * Search categories
     */
    public function searchCategories(string $query): Collection
    {
        return $this->categoryRepository->search($query);
    }

    /**
     * Create new category
     */
    public function createCategory(array $data): Category
    {
        DB::beginTransaction();
        try {
            $category = $this->categoryRepository->create($data);

            // Clear cache
            $this->clearCategoryCache();

            DB::commit();
            return $category;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create category', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update category
     */
    public function updateCategory(int $id, array $data): Category
    {
        DB::beginTransaction();
        try {
            $this->categoryRepository->update($id, $data);
            $category = $this->categoryRepository->findOrFail($id);

            // Clear cache
            $this->clearCategoryCache($category->slug);

            DB::commit();
            return $category;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update category', [
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete category
     */
    public function deleteCategory(int $id): bool
    {
        DB::beginTransaction();
        try {
            $category = $this->categoryRepository->findOrFail($id);

            // Check if category has products
            if ($category->products()->exists()) {
                throw new \Exception('Cannot delete category with associated products');
            }

            // Check if category has children
            if ($category->children()->exists()) {
                throw new \Exception('Cannot delete category with child categories');
            }

            $result = $this->categoryRepository->delete($id);

            // Clear cache
            $this->clearCategoryCache($category->slug);

            DB::commit();
            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete category', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get popular categories
     */
    public function getPopularCategories(int $limit = 10): Collection
    {
        $cacheKey = "categories.popular.{$limit}";

        return Cache::remember($cacheKey, 1800, function () use ($limit) {
            return $this->categoryRepository
                ->getWithProductsCount()
                ->sortByDesc('products_count')
                ->take($limit);
        });
    }

    /**
     * Clear category cache
     */
    protected function clearCategoryCache(?string $slug = null): void
    {
        $patterns = [
            'categories.all',
            'categories.tree',
            'categories.with_products_count',
            'categories.parents',
            'categories.popular.*',
            'categories.children.*',
        ];

        if ($slug) {
            $patterns[] = "category.slug.{$slug}";
        }

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }

        // Clear cache tags if using tagged cache
        if (method_exists(Cache::class, 'tags')) {
            Cache::tags(['categories'])->flush();
        }
    }
}
