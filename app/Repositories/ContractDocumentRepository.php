<?php

namespace App\Repositories;

use App\Models\ContractDocument;
use App\Repositories\Interfaces\ContractDocumentRepositoryInterface;

class ContractDocumentRepository extends BaseRepository implements ContractDocumentRepositoryInterface
{
    /**
     * @param ContractDocument $model
     */
    public function __construct(ContractDocument $model)
    {
        parent::__construct($model);
    }

    /**
     * @param int $contractId
     * @return ContractDocument|null
     */
    public function findByContract(int $contractId): ?ContractDocument
    {
        return $this->newQuery()->where('contract_id', $contractId)->first();
    }

    /**
     * @return ContractDocument|null
     */
    public function findLatest(): ?ContractDocument
    {
        return $this->newQuery()->orderByDesc('id')->first();
    }
}
