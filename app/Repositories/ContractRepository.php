<?php

namespace App\Repositories;

use App\Models\Contract;
use App\Repositories\Interfaces\ContractRepositoryInterface;

class ContractRepository extends BaseRepository implements ContractRepositoryInterface
{
    /**
     * @param Contract $model
     */
    public function __construct(Contract $model)
    {
        parent::__construct($model);
    }
}
