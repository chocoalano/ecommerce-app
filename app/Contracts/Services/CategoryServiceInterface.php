<?php

namespace App\Contracts\Services;

use App\Models\Product\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryServiceInterface
{
    /**
     * Get all active categories
     */
    public function getAllCategories(): Collection;

    /**
     * Get category by slug
     */
    public function getCategoryBySlug(string $slug): ?Category;

    /**
     * Get category tree
     */
    public function getCategoryTree(): Collection;

    /**
     * Get categories with products count
     */
    public function getCategoriesWithProductsCount(): Collection;

    /**
     * Get parent categories
     */
    public function getParentCategories(): Collection;

    /**
     * Get child categories
     */
    public function getChildCategories(int $parentId): Collection;

    /**
     * Search categories
     */
    public function searchCategories(string $query): Collection;

    /**
     * Create new category
     */
    public function createCategory(array $data): Category;

    /**
     * Update category
     */
    public function updateCategory(int $id, array $data): Category;

    /**
     * Delete category
     */
    public function deleteCategory(int $id): bool;

    /**
     * Get popular categories
     */
    public function getPopularCategories(int $limit = 10): Collection;
}
