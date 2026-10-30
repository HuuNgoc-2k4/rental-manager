<?php

namespace App\Services\Manage;

use App\Enums\Tenant\TenantInfoStatus;
use App\Exceptions\BusinessException;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Interfaces\ContractTenantRepositoryInterface;
use App\Repositories\Interfaces\TenantRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TenantService extends BaseService
{
    /** Các trường định danh bị khóa sau khi hồ sơ được xác minh. */
    public const LOCKED_FIELDS = ['full_name', 'date_of_birth', 'gender', 'id_number', 'id_issued_date', 'id_issued_place', 'permanent_address'];

    /** Trường khách vẫn sửa được sau khi hồ sơ đã được xác minh. */
    public const EDITABLE_WHEN_VERIFIED = ['email', 'occupation'];

    protected TenantRepositoryInterface $tenantRepository;

    protected ContractTenantRepositoryInterface $linkRepository;

    /**
     * @param Tenant $model
     * @param TenantRepositoryInterface $tenantRepository
     * @param ContractTenantRepositoryInterface $linkRepository
     */
    public function __construct(Tenant $model, TenantRepositoryInterface $tenantRepository, ContractTenantRepositoryInterface $linkRepository)
    {
        parent::__construct($model);
        $this->tenantRepository = $tenantRepository;
        $this->linkRepository = $linkRepository;
    }

    /**
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getList(Request $request): LengthAwarePaginator
    {
        return $this->tenantRepository->paginate($request->only(['keyword', 'info_status']), $this->getPerPage($request));
    }

    /**
     * Khách chưa ở hộ nào — nguồn cho ô chọn khi cho thuê / thêm người.
     *
     * @return Collection
     */
    public function getAvailable(): Collection
    {
        return $this->tenantRepository->getWithoutLiveHousehold();
    }

    /**
     * Admin tạo hồ sơ: khai đủ định danh thì xác minh luôn, thiếu thì để Chưa khai báo.
     *
     * @param array $data
     * @param array $files ['id_front' => UploadedFile|null, 'id_back' => UploadedFile|null]
     * @param User $actor
     * @return array{status: bool, message: string, tenant?: Tenant}
     */
    public function store(array $data, array $files, User $actor): array
    {
        try {
            DB::beginTransaction();
            $tenant = $this->createProfile($data, $files, $actor);
            DB::commit();

            return ['status' => true, 'message' => "Đã thêm hồ sơ {$tenant->full_name}.", 'tenant' => $tenant];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create tenant error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi thêm hồ sơ khách.'];
        }
    }

    /**
     * Tạo hồ sơ trong transaction đang mở của caller (dùng lại khi cho thuê / chủ hộ thêm người).
     *
     * @param array $data
     * @param array $files
     * @param User $actor
     * @return Tenant
     */
    public function createProfile(array $data, array $files, User $actor): Tenant
    {
        $payload = $this->payload($data);
        $payload['info_status'] = match (true) {
            !$this->hasFullIdentity($payload) => TenantInfoStatus::Draft->value,
            $actor->isAdmin() => TenantInfoStatus::Verified->value,
            default => TenantInfoStatus::Submitted->value
        };

        if ($payload['info_status'] === TenantInfoStatus::Verified->value) {
            $payload['info_verified_at'] = now();
            $payload['info_verified_by'] = $actor->id;
        }

        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->create($payload);
        $this->storeImages($tenant, $files);

        return $tenant;
    }

    /**
     * Admin sửa mọi hồ sơ, mọi lúc.
     *
     * @param Tenant $tenant
     * @param array $data
     * @param array $files
     * @param User $actor
     * @return array{status: bool, message: string}
     */
    public function update(Tenant $tenant, array $data, array $files, User $actor): array
    {
        try {
            DB::beginTransaction();

            $payload = $this->payload($data);
            if (!$tenant->isVerified() && $this->hasFullIdentity($payload)) {
                $payload += [
                    'info_status' => TenantInfoStatus::Verified->value,
                    'info_verified_at' => now(),
                    'info_verified_by' => $actor->id
                ];
            }

            $this->tenantRepository->update($tenant, $payload);
            $this->storeImages($tenant, $files);

            DB::commit();

            return ['status' => true, 'message' => 'Đã cập nhật hồ sơ khách.'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update tenant error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi cập nhật hồ sơ khách.'];
        }
    }

    /**
     * Chỉ xóa được hồ sơ chưa từng có trong hợp đồng nào.
     *
     * @param Tenant $tenant
     * @return array{status: bool, message: string}
     */
    public function delete(Tenant $tenant): array
    {
        try {
            DB::beginTransaction();

            if ($this->tenantRepository->hasContractHistory($tenant)) {
                throw new BusinessException('Khách này đã có lịch sử thuê nên không thể xóa.', 'tenant_has_history');
            }

            Storage::disk()->deleteDirectory("tenants/{$tenant->id}");
            $this->tenantRepository->delete($tenant);
            DB::commit();

            return ['status' => true, 'message' => 'Đã xóa hồ sơ khách.'];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete tenant error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi xóa hồ sơ khách.'];
        }
    }

    /**
     * Khai báo thông tin (khách tự khai hoặc chủ hộ khai hộ) rồi gửi duyệt. Người gọi tự kiểm quyền.
     * - Đã Verified: chỉ sửa được email và nghề nghiệp, trường định danh và ảnh bị khóa.
     * - Chưa Verified: phải đủ họ tên, ngày sinh, CCCD, thường trú và cả 2 ảnh → chuyển Submitted.
     *
     * @param Tenant $tenant
     * @param array $data
     * @param array $files
     * @return array{status: bool, message: string}
     */
    public function declare(Tenant $tenant, array $data, array $files): array
    {
        try {
            DB::beginTransaction();

            if ($tenant->isVerified()) {
                $this->tenantRepository->update($tenant, array_intersect_key($this->payload($data), array_flip(self::EDITABLE_WHEN_VERIFIED)));
                DB::commit();

                return ['status' => true, 'message' => 'Đã cập nhật thông tin liên hệ.'];
            }

            $payload = array_diff_key($this->payload($data), array_flip(['phone', 'note']));
            $hasImages = ($tenant->id_front_path || ($files['id_front'] ?? null)) && ($tenant->id_back_path || ($files['id_back'] ?? null));

            // Kiểm tra đủ hồ sơ TRƯỚC khi ghi file để không để lại ảnh mồ côi khi bị từ chối.
            if (!$this->hasFullIdentity($payload) || !$hasImages) {
                throw new BusinessException('Vui lòng khai đủ họ tên, ngày sinh, số CCCD, địa chỉ thường trú và tải ảnh CCCD hai mặt.', 'declaration_incomplete');
            }

            $this->tenantRepository->update($tenant, $payload + [
                'info_status' => TenantInfoStatus::Submitted->value,
                'info_reject_reason' => null
            ]);
            $this->storeImages($tenant, $files);

            DB::commit();

            return ['status' => true, 'message' => 'Đã gửi khai báo, chủ nhà sẽ xem xét.'];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Declare tenant error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi gửi khai báo.'];
        }
    }

    /**
     * Ai được xem ảnh CCCD (ma trận phân quyền mục 6): admin; chính chủ; chủ hộ với người do mình khai hộ.
     *
     * @param User $user
     * @param Tenant $tenant
     * @return bool
     */
    public function canViewIdImage(User $user, Tenant $tenant): bool
    {
        return $user->isAdmin()
            || $tenant->user_id === $user->id
            || $this->isDeclaredByOwner($user, $tenant);
    }

    /**
     * Chủ hộ khai hộ người này: cùng hộ, người đó chưa có tài khoản và dòng hộ do chính chủ hộ tạo.
     *
     * @param User $owner
     * @param Tenant $tenant
     * @return bool
     */
    public function isDeclaredByOwner(User $owner, Tenant $tenant): bool
    {
        if (!$owner->isOwner() || $tenant->user_id) {
            return false;
        }

        $mine = $this->linkRepository->findLiveLinkForUser($owner->id);
        $theirs = $this->linkRepository->findLiveLinkForTenant($tenant->id);

        return $mine && $theirs && $mine->contract_id === $theirs->contract_id && $theirs->created_by === $owner->id;
    }

    /**
     * @param Tenant $tenant
     * @param string $side front|back
     * @return string|null Path trên disk mặc định (private: local hoặc S3), null nếu chưa có ảnh
     */
    public function idImagePath(Tenant $tenant, string $side): ?string
    {
        $path = $side === 'front' ? $tenant->id_front_path : $tenant->id_back_path;

        return $path && Storage::disk()->exists($path) ? $path : null;
    }

    /**
     * Lưu ảnh CCCD mới và xóa file cũ.
     *
     * @param Tenant $tenant
     * @param array $files
     * @return void
     */
    public function storeImages(Tenant $tenant, array $files): void
    {
        $columns = ['id_front' => 'id_front_path', 'id_back' => 'id_back_path'];

        foreach ($columns as $key => $column) {
            $file = $files[$key] ?? null;
            if (!$file instanceof UploadedFile) {
                continue;
            }

            if ($tenant->{$column}) {
                Storage::disk()->delete($tenant->{$column});
            }

            $tenant->{$column} = $file->store("tenants/{$tenant->id}");
        }

        if ($tenant->isDirty()) {
            $tenant->save();
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function payload(array $data): array
    {
        $payload = [];
        foreach (['full_name', 'phone', 'email', 'date_of_birth', 'gender', 'id_number', 'id_issued_date', 'id_issued_place', 'permanent_address', 'occupation', 'note'] as $field) {
            if (array_key_exists($field, $data)) {
                $value = is_string($data[$field]) ? trim($data[$field]) : $data[$field];
                $payload[$field] = $value === '' ? null : $value;
            }
        }

        return $payload;
    }

    /**
     * @param array $payload
     * @return bool
     */
    private function hasFullIdentity(array $payload): bool
    {
        foreach (['full_name', 'date_of_birth', 'id_number', 'permanent_address'] as $field) {
            if (empty($payload[$field])) {
                return false;
            }
        }

        return true;
    }
}
