<?php

namespace App\Services\Manage;

use App\Enums\Contract\ContractDocumentStatus;
use App\Enums\Contract\ContractStatus;
use App\Enums\Contract\MemberApproval;
use App\Exceptions\BusinessException;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Interfaces\ContractDocumentRepositoryInterface;
use App\Repositories\Interfaces\ContractRepositoryInterface;
use App\Repositories\Interfaces\ContractTenantRepositoryInterface;
use App\Repositories\Interfaces\RoomRepositoryInterface;
use App\Services\BaseService;
use App\Services\Pdf\PdfService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Văn bản hợp đồng thuê: tự lập từ hộ + phòng, admin sửa, chủ hộ duyệt, đã duyệt thì khóa và in PDF / Word.
 * Văn bản là bản chụp: đổi hồ sơ khách / giá phòng về sau không làm đổi văn bản đã lập.
 */
class ContractDocumentService extends BaseService
{
    /** Mặc định khi chưa có văn bản nào trước đó để lấy lại đơn giá. */
    const DEFAULT_ELECTRIC_PRICE = 3500;

    const DEFAULT_WATER_PRICE = 20000;

    const DEFAULT_PAYMENT_DAY = 5;

    const DEFAULT_DEPOSIT_MONTHS = 1;

    const DEFAULT_PAYMENT_METHOD = 'Tiền mặt hoặc chuyển khoản';

    const DEFAULT_SERVICES = [
        ['name' => 'Internet', 'amount' => 100000, 'unit' => 'phòng/tháng'],
        ['name' => 'Vệ sinh, thu gom rác', 'amount' => 30000, 'unit' => 'người/tháng'],
        ['name' => 'Gửi xe máy', 'amount' => 100000, 'unit' => 'xe/tháng']
    ];

    /** Hậu tố số hợp đồng: 001/2026/HĐTP. */
    const CODE_SUFFIX = 'HĐTP';

    const PARTY_FIELDS = ['full_name', 'date_of_birth', 'id_number', 'id_issued_date', 'id_issued_place', 'permanent_address', 'phone'];

    const MEMBER_FIELDS = ['full_name', 'date_of_birth', 'id_number', 'phone'];

    const SERVICE_FIELDS = ['name', 'amount', 'unit'];

    /** Cột admin được sửa trực tiếp (ngoài landlord / tenant / members / services đã chuẩn hóa riêng). */
    const EDITABLE_FIELDS = [
        'code', 'signed_date', 'signed_place', 'start_date', 'end_date', 'property_address', 'room_name', 'room_area',
        'max_occupants', 'equipment', 'rent', 'deposit', 'electric_price', 'water_price', 'payment_day', 'payment_method', 'extra_terms'
    ];

    /**
     * @param ContractDocument $model
     * @param ContractDocumentRepositoryInterface $documentRepository
     * @param ContractRepositoryInterface $contractRepository
     * @param ContractTenantRepositoryInterface $linkRepository
     * @param RoomRepositoryInterface $roomRepository
     * @param PdfService $pdfService
     */
    public function __construct(
        ContractDocument $model,
        protected ContractDocumentRepositoryInterface $documentRepository,
        protected ContractRepositoryInterface $contractRepository,
        protected ContractTenantRepositoryInterface $linkRepository,
        protected RoomRepositoryInterface $roomRepository,
        protected PdfService $pdfService
    ) {
        parent::__construct($model);
    }

    /**
     * @param int $contractId
     * @return ContractDocument|null
     */
    public function findByContract(int $contractId): ?ContractDocument
    {
        return $this->documentRepository->findByContract($contractId);
    }

    /**
     * Lập văn bản nháp tự động từ hộ đang ở và phòng. Bên A, đơn giá, phí dịch vụ lấy lại từ văn bản gần nhất.
     *
     * @param Contract $contract
     * @param User $actor
     * @return array{status: bool, message: string, document?: ContractDocument}
     */
    public function generate(Contract $contract, User $actor): array
    {
        try {
            DB::beginTransaction();

            if (!ContractStatus::isActive($contract->status)) {
                throw new BusinessException('Hợp đồng đã kết thúc, không lập văn bản mới.', 'contract_ended');
            }
            if ($this->documentRepository->findByContract($contract->id)) {
                throw new BusinessException('Hợp đồng này đã có văn bản.', 'document_exists');
            }

            $previous = $this->documentRepository->findLatest();

            $snapshot = $this->snapshotHousehold($contract);
            $document = $this->documentRepository->create($snapshot + [
                'contract_id' => $contract->id,
                // Số tạm, đổi thành số theo id ngay dưới khi đã có id.
                'code' => (string) $contract->id,
                'status' => ContractDocumentStatus::Draft->value,
                'signed_date' => now()->toDateString(),
                'signed_place' => $snapshot['property_address'],
                'start_date' => $contract->start_date->toDateString(),
                'end_date' => $contract->end_date?->toDateString(),
                'landlord' => $previous?->landlord ?? $this->emptyParty(['full_name' => $actor->name, 'phone' => $actor->phone]),
                'deposit' => $snapshot['rent'] * self::DEFAULT_DEPOSIT_MONTHS,
                'electric_price' => $previous?->electric_price ?? self::DEFAULT_ELECTRIC_PRICE,
                'water_price' => $previous?->water_price ?? self::DEFAULT_WATER_PRICE,
                'services' => $previous?->services ?? self::DEFAULT_SERVICES,
                'payment_day' => $previous?->payment_day ?? self::DEFAULT_PAYMENT_DAY,
                'payment_method' => $previous?->payment_method ?? self::DEFAULT_PAYMENT_METHOD,
                'created_by' => $actor->id
            ]);
            $this->documentRepository->update($document, [
                'code' => sprintf('%03d/%s/%s', $document->id, now()->year, self::CODE_SUFFIX)
            ]);

            DB::commit();

            return ['status' => true, 'message' => 'Đã lập hợp đồng nháp từ thông tin phòng và hộ thuê.', 'document' => $document];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Generate contract document error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi lập hợp đồng.'];
        }
    }

    /**
     * Admin sửa văn bản. Mọi lần sửa đưa văn bản về nháp: bản chủ hộ đang xem / đã yêu cầu sửa phải gửi lại.
     *
     * @param ContractDocument $document
     * @param array $data
     * @return array{status: bool, message: string}
     */
    public function update(ContractDocument $document, array $data): array
    {
        try {
            DB::beginTransaction();

            $this->assertEditable($document);

            $fields = array_intersect_key($data, array_flip(self::EDITABLE_FIELDS)) + [
                'landlord' => $this->pick($data['landlord'] ?? [], self::PARTY_FIELDS),
                'tenant' => $this->pick($data['tenant'] ?? [], self::PARTY_FIELDS),
                'members' => $this->pickRows($data['members'] ?? [], self::MEMBER_FIELDS),
                'services' => array_map(
                    fn ($row) => ['amount' => (int) $row['amount']] + $row,
                    $this->pickRows($data['services'] ?? [], self::SERVICE_FIELDS)
                ),
                'status' => ContractDocumentStatus::Draft->value
            ];
            $this->documentRepository->update($document, $fields);

            DB::commit();

            return ['status' => true, 'message' => 'Đã lưu hợp đồng (bản nháp).'];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update contract document error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi lưu hợp đồng.'];
        }
    }

    /**
     * Lấy lại Bên B, người ở cùng và thông tin phòng từ dữ liệu hiện tại (sau khi khách khai báo xong / thêm người).
     *
     * @param ContractDocument $document
     * @return array{status: bool, message: string}
     */
    public function refreshFromHousehold(ContractDocument $document): array
    {
        try {
            DB::beginTransaction();

            $this->assertEditable($document);

            $contract = $this->contractRepository->findOrFail($document->contract_id);
            $snapshot = $this->snapshotHousehold($contract);
            // Giá thuê giữ theo bản admin đã sửa, chỉ làm mới thông tin người và phòng.
            unset($snapshot['rent']);
            $this->documentRepository->update($document, $snapshot + ['status' => ContractDocumentStatus::Draft->value]);

            DB::commit();

            return ['status' => true, 'message' => 'Đã cập nhật Bên B, người ở cùng và thông tin phòng theo hồ sơ hiện tại.'];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Refresh contract document error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi cập nhật hợp đồng.'];
        }
    }

    /**
     * Gửi chủ hộ duyệt.
     *
     * @param ContractDocument $document
     * @return array{status: bool, message: string}
     */
    public function submit(ContractDocument $document): array
    {
        try {
            DB::beginTransaction();

            if (!ContractDocumentStatus::isDraft($document->status) && !ContractDocumentStatus::isRejected($document->status)) {
                throw new BusinessException('Chỉ gửi duyệt được bản nháp hoặc bản chủ hộ yêu cầu sửa.', 'not_submittable');
            }
            if (!ContractStatus::isActive($this->contractRepository->findOrFail($document->contract_id)->status)) {
                throw new BusinessException('Hợp đồng không còn hiệu lực, không gửi duyệt được.', 'contract_ended');
            }

            $this->documentRepository->update($document, [
                'status' => ContractDocumentStatus::Pending->value,
                'reject_reason' => null,
                'submitted_at' => now()
            ]);

            DB::commit();

            return ['status' => true, 'message' => 'Đã gửi hợp đồng cho chủ hộ duyệt.'];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Submit contract document error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi gửi duyệt hợp đồng.'];
        }
    }

    /**
     * Chủ hộ duyệt. $version là updated_at chủ hộ đã xem: admin sửa sau đó thì từ chối duyệt bản cũ.
     * Quyền chủ hộ với văn bản do HouseholdService kiểm tra trước khi gọi.
     *
     * @param ContractDocument $document
     * @param User $owner
     * @param string|null $version
     * @return array{status: bool, message: string}
     */
    public function approve(ContractDocument $document, User $owner, ?string $version): array
    {
        try {
            DB::beginTransaction();

            $this->assertPending($document, $version);

            $this->documentRepository->update($document, [
                'status' => ContractDocumentStatus::Approved->value,
                'approved_at' => now(),
                'approved_by' => $owner->id
            ]);

            DB::commit();

            return ['status' => true, 'message' => 'Bạn đã duyệt hợp đồng thuê phòng.'];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approve contract document error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi duyệt hợp đồng.'];
        }
    }

    /**
     * Chủ hộ yêu cầu sửa, kèm lý do để admin biết sửa gì.
     *
     * @param ContractDocument $document
     * @param string $reason
     * @param string|null $version
     * @return array{status: bool, message: string}
     */
    public function reject(ContractDocument $document, string $reason, ?string $version): array
    {
        try {
            DB::beginTransaction();

            $this->assertPending($document, $version);

            $this->documentRepository->update($document, [
                'status' => ContractDocumentStatus::Rejected->value,
                'reject_reason' => $reason
            ]);

            DB::commit();

            return ['status' => true, 'message' => 'Đã gửi yêu cầu sửa hợp đồng cho chủ nhà.'];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reject contract document error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi gửi yêu cầu sửa.'];
        }
    }

    /**
     * @param ContractDocument $document
     * @return array{content: string, file_name: string}
     *
     * @throws BusinessException
     */
    public function exportPdf(ContractDocument $document): array
    {
        $this->assertPrintable($document);
        $html = view('contracts.print', ['document' => $document, 'fontFamily' => PdfService::FONT, 'forWord' => false])->render();

        return [
            'content' => $this->pdfService->render($html, 'Hợp đồng thuê phòng '.$document->code),
            'file_name' => $this->fileName($document, 'pdf')
        ];
    }

    /**
     * Word mở trực tiếp file HTML đặt đuôi .doc → dùng chung template với PDF, không cần thư viện Word.
     *
     * @param ContractDocument $document
     * @return array{content: string, file_name: string}
     *
     * @throws BusinessException
     */
    public function exportWord(ContractDocument $document): array
    {
        $this->assertPrintable($document);

        return [
            'content' => view('contracts.print', ['document' => $document, 'fontFamily' => "'Times New Roman', serif", 'forWord' => true])->render(),
            'file_name' => $this->fileName($document, 'doc')
        ];
    }

    /**
     * Bên B, người ở cùng (đã duyệt, chưa rời), thông tin phòng và checklist nội thất tại thời điểm gọi.
     *
     * @param Contract $contract
     * @return array{tenant: array, members: array, property_address: string, room_name: string, room_area: mixed, max_occupants: int, equipment_items: array, rent: int}
     *
     * @throws BusinessException
     */
    private function snapshotHousehold(Contract $contract): array
    {
        $residents = $this->linkRepository->getLiveLinks($contract)
            ->filter(fn ($link) => MemberApproval::isApproved($link->approval_status));
        $primary = $residents->firstWhere('is_primary', true);
        if (!$primary) {
            throw new BusinessException('Hợp đồng chưa có chủ hộ đang ở.', 'no_primary');
        }

        $room = $this->roomRepository->findOrFail($contract->room_id)->load('property');

        return [
            'tenant' => $this->partyFromTenant($primary->tenant),
            'members' => $residents->reject(fn ($link) => $link->is_primary)
                ->map(fn ($link) => $this->pick($this->partyFromTenant($link->tenant), self::MEMBER_FIELDS))
                ->values()
                ->all(),
            'property_address' => $room->property->address,
            'room_name' => $room->name,
            'room_area' => $room->area,
            // Phòng không giới hạn số người; điều khoản 1.2 của văn bản mặc định theo số người đang ở, admin sửa nếu cần.
            'max_occupants' => $residents->count(),
            'equipment_items' => $room->furniture ?? [],
            'rent' => (int) $room->base_rent
        ];
    }

    /**
     * @param Tenant $tenant
     * @return array
     */
    private function partyFromTenant(Tenant $tenant): array
    {
        return [
            'full_name' => $tenant->full_name,
            'date_of_birth' => $tenant->date_of_birth?->toDateString(),
            'id_number' => $tenant->id_number,
            'id_issued_date' => $tenant->id_issued_date?->toDateString(),
            'id_issued_place' => $tenant->id_issued_place,
            'permanent_address' => $tenant->permanent_address,
            'phone' => $tenant->phone
        ];
    }

    /**
     * @param array $values
     * @return array
     */
    private function emptyParty(array $values): array
    {
        return $values + array_fill_keys(self::PARTY_FIELDS, null);
    }

    /**
     * Chỉ giữ các khóa cho phép (JSON nhận từ form không được mang khóa lạ vào DB).
     *
     * @param array $row
     * @param array $fields
     * @return array
     */
    private function pick(array $row, array $fields): array
    {
        return array_merge(array_fill_keys($fields, null), array_intersect_key($row, array_flip($fields)));
    }

    /**
     * @param array $rows
     * @param array $fields
     * @return array
     */
    private function pickRows(array $rows, array $fields): array
    {
        return array_values(array_map(fn ($row) => $this->pick((array) $row, $fields), $rows));
    }

    /**
     * @param ContractDocument $document
     * @return void
     *
     * @throws BusinessException
     */
    private function assertEditable(ContractDocument $document): void
    {
        if ($document->isApproved()) {
            throw new BusinessException('Hợp đồng đã được chủ hộ duyệt, không sửa được nữa.', 'document_locked');
        }
    }

    /**
     * @param ContractDocument $document
     * @param string|null $version
     * @return void
     *
     * @throws BusinessException
     */
    private function assertPending(ContractDocument $document, ?string $version): void
    {
        if (!ContractDocumentStatus::isPending($document->status)) {
            throw new BusinessException('Hợp đồng không ở trạng thái chờ duyệt.', 'not_pending');
        }
        if ($version !== (string) $document->updated_at?->timestamp) {
            throw new BusinessException('Chủ nhà vừa cập nhật hợp đồng, vui lòng xem lại bản mới.', 'stale_version');
        }
    }

    /**
     * @param ContractDocument $document
     * @return void
     *
     * @throws BusinessException
     */
    private function assertPrintable(ContractDocument $document): void
    {
        if (!$document->isApproved()) {
            throw new BusinessException('Chỉ in được hợp đồng đã được chủ hộ duyệt.', 'not_approved');
        }
    }

    /**
     * @param ContractDocument $document
     * @param string $extension
     * @return string
     */
    private function fileName(ContractDocument $document, string $extension): string
    {
        // Tên file ASCII cho header Content-Disposition; "/" trong số HĐ đổi thành "-" để số không dính liền.
        return 'hop-dong-thue-phong-'.Str::slug(str_replace('/', '-', $document->room_name.'-'.$document->code)).'.'.$extension;
    }
}
