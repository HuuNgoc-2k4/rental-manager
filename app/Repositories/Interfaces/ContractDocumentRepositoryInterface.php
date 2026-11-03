<?php

namespace App\Repositories\Interfaces;

use App\Models\ContractDocument;

interface ContractDocumentRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param int $contractId
     * @return ContractDocument|null
     */
    public function findByContract(int $contractId): ?ContractDocument;

    /**
     * Văn bản lập gần nhất — nguồn mặc định cho Bên A, đơn giá và phí dịch vụ của văn bản mới.
     *
     * @return ContractDocument|null
     */
    public function findLatest(): ?ContractDocument;
}
