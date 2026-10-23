<?php

namespace App\Repositories;

use App\Enums\Contract\ContractStatus;
use App\Enums\Contract\MemberApproval;
use App\Models\Contract;
use App\Models\ContractTenant;
use App\Repositories\Interfaces\ContractTenantRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ContractTenantRepository extends BaseRepository implements ContractTenantRepositoryInterface
{
    /**
     * @param ContractTenant $model
     */
    public function __construct(ContractTenant $model)
    {
        parent::__construct($model);
    }

    /**
     * @param Contract $contract
     * @return Collection
     */
    public function getLiveLinks(Contract $contract): Collection
    {
        return $this->newQuery()
            ->where('contract_id', $contract->id)
            ->whereNull('left_at')
            ->with('tenant.user')
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param int $tenantId
     * @return ContractTenant|null
     */
    public function findLiveLinkForTenant(int $tenantId): ?ContractTenant
    {
        return $this->newQuery()
            ->where('tenant_id', $tenantId)
            ->whereNull('left_at')
            ->whereHas('contract', fn ($q) => $q->where('status', ContractStatus::Active->value))
            ->first();
    }

    /**
     * @param Contract $contract
     * @param bool $includePending
     * @return int
     */
    public function countLive(Contract $contract, bool $includePending): int
    {
        $query = $this->newQuery()->where('contract_id', $contract->id)->whereNull('left_at');

        if (!$includePending) {
            $query->where('approval_status', MemberApproval::Approved->value);
        }

        return $query->count();
    }
}
