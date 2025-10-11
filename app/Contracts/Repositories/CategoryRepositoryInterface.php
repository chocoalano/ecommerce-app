<?php

namespace App\Contracts\Repositories;

use App\Models\Product\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find category by slug
     */
    public function findBySlug(string $slug): ?Category;

    /**
     * Get active categories
     */
    public function getActive(): Collection;

    /**
     * Get categories with products count
     */
    public function getWithProductsCount(): Collection;

    /**
     * Get parent categories
     */
    public function getParents(): Collection;

    /**
     * Get child categories
     */
    public function getChildren(int $parentId): Collection;

    /**
     * Get category tree
     */
    public function getTree(): Collection;

    /**
     * Get categories by level
     */
    public function getByLevel(int $level): Collection;

    /**
     * Search categories
     */
    public function search(string $query): Collection;
}
