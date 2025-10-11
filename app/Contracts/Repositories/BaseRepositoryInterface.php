<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * Get all records
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Find record by ID
     */
    public function find(int $id, array $columns = ['*']): ?Model;

    /**
     * Find record by ID or fail
     */
    public function findOrFail(int $id, array $columns = ['*']): Model;

    /**
     * Find by specific field
     */
    public function findBy(string $field, $value, array $columns = ['*']): ?Model;

    /**
     * Find where conditions
     */
    public function findWhere(array $criteria, array $columns = ['*']): Collection;

    /**
     * Find first where conditions
     */
    public function findWhereFirst(array $criteria, array $columns = ['*']): ?Model;

    /**
     * Create new record
     */
    public function create(array $data): Model;

    /**
     * Update record
     */
    public function update(int $id, array $data): bool;

    /**
     * Update or create record
     */
    public function updateOrCreate(array $criteria, array $data): Model;

    /**
     * Delete record
     */
    public function delete(int $id): bool;

    /**
     * Paginate results
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Get with relationships
     */
    public function with(array $relations): self;

    /**
     * Where conditions
     */
    public function where(string $field, $operator = null, $value = null): self;

    /**
     * Order by
     */
    public function orderBy(string $field, string $direction = 'asc'): self;

    /**
     * Limit results
     */
    public function limit(int $limit): self;

    /**
     * Get fresh model instance
     */
    public function getModel(): Model;

    /**
     * Reset query builder
     */
    public function resetQuery(): self;
}
