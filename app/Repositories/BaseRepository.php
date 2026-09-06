<?php

namespace App\Repositories;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param int $id
     * @return Model|null
     */
    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * @param int $id
     * @return Model
     */
    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * @param Model $model
     * @param array $data
     * @return bool
     */
    public function update(Model $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * @param Model $model
     * @return bool
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * @param array $filters Mảng điều kiện dạng [[column, operator, value], ...]
     * @param array $whereIn
     * @return Collection
     */
    public function findByConditions(array $filters = [], array $whereIn = []): Collection
    {
        $query = $this->newQuery();

        if (!empty($filters)) {
            $query->where($filters);
        }

        foreach ($whereIn as $column => $values) {
            if (is_array($values) && count($values) > 0) {
                $query->whereIn($column, $values);
            }
        }

        return $query->get();
    }

    /**
     * @return Builder
     */
    protected function newQuery(): Builder
    {
        return $this->model->newQuery();
    }
}
