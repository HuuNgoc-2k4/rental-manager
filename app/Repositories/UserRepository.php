<?php

namespace App\Repositories;

use App\Enums\User\UserStatus;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery();
        $this->applyFilters($query, $filters);

        return $query->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param string $login Email hoặc số điện thoại
     * @return User|null
     */
    public function findByLogin(string $login): ?User
    {
        return $this->newQuery()
            ->where('email', $login)
            ->orWhere('phone', $login)
            ->first();
    }

    /**
     * @return Collection
     */
    public function getPending(): Collection
    {
        return $this->newQuery()
            ->where('status', UserStatus::Pending->value)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @param Builder $query
     * @param array $filters
     * @return void
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['keyword'])) {
            $keyword = '%'.$filters['keyword'].'%';
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('name', 'like', $keyword)
                    ->orWhere('email', 'like', $keyword)
                    ->orWhere('phone', 'like', $keyword);
            });
        }

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }
}
