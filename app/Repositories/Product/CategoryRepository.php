<?php

namespace App\Repositories\Product;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Product\Category;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    /**
     * Get fresh model instance
     */
    public function getModel(): Model
    {
        return new Category();
    }

    /**
     * Find category by slug
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->findBy('slug', $slug);
    }

    /**
     * Get active categories
     */
    public function getActive(): Collection
    {
        return $this->where('is_active', true)->all();
    }

    /**
     * Get categories with products count
     */
    public function getWithProductsCount(): Collection
    {
        return $this->query
            ->withCount(['products' => function ($query) {
                $query->where('is_active', true);
            }])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get parent categories
     */
    public function getParents(): Collection
    {
        return $this->query
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get child categories
     */
    public function getChildren(int $parentId): Collection
    {
        return $this->query
            ->where('parent_id', $parentId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get category tree
     */
    public function getTree(): Collection
    {
        return $this->query
            ->with(['children' => function ($query) {
                $query->where('is_active', true)
                      ->orderBy('sort_order')
                      ->orderBy('name');
            }])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get categories by level
     */
    public function getByLevel(int $level): Collection
    {
        return $this->query
            ->where('level', $level)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Search categories
     */
    public function search(string $query): Collection
    {
        return $this->query
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->orderBy('name')
            ->get();
    }
}
