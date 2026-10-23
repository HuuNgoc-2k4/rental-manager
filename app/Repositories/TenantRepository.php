<?php

namespace App\Repositories;

use App\Models\Tenant;
use App\Repositories\Interfaces\TenantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TenantRepository extends BaseRepository implements TenantRepositoryInterface
{
    /**
     * @param Tenant $model
     */
    public function __construct(Tenant $model)
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

        if (!empty($filters['keyword'])) {
            $keyword = '%'.$filters['keyword'].'%';
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('full_name', 'like', $keyword)
                    ->orWhere('phone', 'like', $keyword)
                    ->orWhere('id_number', 'like', $keyword);
            });
        }

        if (!empty($filters['info_status'])) {
            $query->where('info_status', $filters['info_status']);
        }

        return $query->orderByDesc('id')->paginate($perPage)->withQueryString();
    }

    /**
     * @param Tenant $tenant
     * @return bool
     */
    public function hasContractHistory(Tenant $tenant): bool
    {
        return $tenant->contractTenants()->exists();
    }
}
