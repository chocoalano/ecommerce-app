<?php

namespace App\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;
    protected Builder $query;

    public function __construct()
    {
        $this->model = $this->getModel();
        $this->resetQuery();
    }

    /**
     * Get fresh model instance
     */
    abstract public function getModel(): Model;

    /**
     * Get all records
     */
    public function all(array $columns = ['*']): Collection
    {
        $result = $this->query->get($columns);
        $this->resetQuery();
        return $result;
    }

    /**
     * Find record by ID
     */
    public function find(int $id, array $columns = ['*']): ?Model
    {
        $result = $this->query->find($id, $columns);
        $this->resetQuery();
        return $result;
    }

    /**
     * Find record by ID or fail
     */
    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        $result = $this->query->findOrFail($id, $columns);
        $this->resetQuery();
        return $result;
    }

    /**
     * Find by specific field
     */
    public function findBy(string $field, $value, array $columns = ['*']): ?Model
    {
        $result = $this->query->where($field, $value)->first($columns);
        $this->resetQuery();
        return $result;
    }

    /**
     * Find where conditions
     */
    public function findWhere(array $criteria, array $columns = ['*']): Collection
    {
        $this->applyCriteria($criteria);
        $result = $this->query->get($columns);
        $this->resetQuery();
        return $result;
    }

    /**
     * Find first where conditions
     */
    public function findWhereFirst(array $criteria, array $columns = ['*']): ?Model
    {
        $this->applyCriteria($criteria);
        $result = $this->query->first($columns);
        $this->resetQuery();
        return $result;
    }

    /**
     * Create new record
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update record
     */
    public function update(int $id, array $data): bool
    {
        $model = $this->findOrFail($id);
        return $model->update($data);
    }

    /**
     * Update or create record
     */
    public function updateOrCreate(array $criteria, array $data): Model
    {
        return $this->model->updateOrCreate($criteria, $data);
    }

    /**
     * Delete record
     */
    public function delete(int $id): bool
    {
        $model = $this->findOrFail($id);
        return $model->delete();
    }

    /**
     * Paginate results
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        $result = $this->query->paginate($perPage, $columns);
        $this->resetQuery();
        return $result;
    }

    /**
     * Get with relationships
     */
    public function with(array $relations): self
    {
        $this->query = $this->query->with($relations);
        return $this;
    }

    /**
     * Where conditions
     */
    public function where(string $field, $operator = null, $value = null): self
    {
        $this->query = $this->query->where($field, $operator, $value);
        return $this;
    }

    /**
     * Order by
     */
    public function orderBy(string $field, string $direction = 'asc'): self
    {
        $this->query = $this->query->orderBy($field, $direction);
        return $this;
    }

    /**
     * Limit results
     */
    public function limit(int $limit): self
    {
        $this->query = $this->query->limit($limit);
        return $this;
    }

    /**
     * Reset query builder
     */
    public function resetQuery(): self
    {
        $this->query = $this->model->newQuery();
        return $this;
    }

    /**
     * Apply criteria to query
     */
    protected function applyCriteria(array $criteria): void
    {
        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $this->query->whereIn($field, $value);
            } else {
                $this->query->where($field, $value);
            }
        }
    }

    /**
     * Get query builder
     */
    protected function getQuery(): Builder
    {
        return $this->query;
    }
}
