<?php

namespace App\Services\Admin;

use App\Enums\User\UserStatus;
use App\Exceptions\BusinessException;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserService extends BaseService
{
    protected UserRepositoryInterface $userRepository;

    /**
     * @param User $model
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(User $model, UserRepositoryInterface $userRepository)
    {
        parent::__construct($model);
        $this->userRepository = $userRepository;
    }

    /**
     * Danh sách user có phân trang + lọc theo từ khóa / vai trò / trạng thái.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getList(Request $request): LengthAwarePaginator
    {
        $filters = $request->only(['keyword', 'role', 'status']);

        return $this->userRepository->paginate($filters, $this->getPerPage($request));
    }

    /**
     * @return Collection
     */
    public function getPendingUsers(): Collection
    {
        return $this->userRepository->getPending();
    }

    /**
     * @param array $data
     * @return array{status: bool, message: string}
     */
    public function store(array $data): array
    {
        try {
            DB::beginTransaction();

            $this->userRepository->create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'role' => $data['role'],
                'status' => $data['status'] ?? UserStatus::Active->value
            ]);

            DB::commit();

            return ['status' => true, 'message' => 'Tạo tài khoản thành công.'];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create user error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi tạo tài khoản.'];
        }
    }

    /**
     * Cập nhật thông tin user. Mật khẩu để trống nghĩa là giữ nguyên.
     *
     * @param User $user
     * @param array $data
     * @return array{status: bool, message: string}
     */
    public function update(User $user, array $data): array
    {
        try {
            DB::beginTransaction();

            $payload = [
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'role' => $data['role'],
                'status' => $data['status']
            ];

            if (!empty($data['password'])) {
                $payload['password'] = $data['password'];
            }

            $this->guardLastAdmin($user, $payload['role'], $payload['status']);
            $this->userRepository->update($user, $payload);

            DB::commit();

            return ['status' => true, 'message' => 'Cập nhật tài khoản thành công.'];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update user error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi cập nhật tài khoản.'];
        }
    }

    /**
     * Đổi trạng thái tài khoản — duyệt chủ hộ mới đăng ký, khóa hoặc mở lại tài khoản.
     *
     * @param User $user
     * @param string $status
     * @return array{status: bool, message: string}
     */
    public function updateStatus(User $user, string $status): array
    {
        try {
            $this->guardLastAdmin($user, $user->role, $status);
            $this->userRepository->update($user, ['status' => $status]);

            $label = UserStatus::from($status)->label();

            return ['status' => true, 'message' => "Tài khoản {$user->name} đã chuyển sang trạng thái: {$label}."];
        } catch (BusinessException $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            Log::error('Update user status error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi đổi trạng thái tài khoản.'];
        }
    }

    /**
     * Không cho tự khóa mình hoặc hạ vai trò admin cuối cùng — mất đường vào hệ thống.
     *
     * @param User $user
     * @param string $newRole
     * @param string $newStatus
     * @return void
     *
     * @throws BusinessException
     */
    private function guardLastAdmin(User $user, string $newRole, string $newStatus): void
    {
        if (!$user->isAdmin()) {
            return;
        }

        $stillActiveAdmin = $user->isAdmin()
            && $newRole === $user->role
            && UserStatus::isActive($newStatus);

        if ($stillActiveAdmin) {
            return;
        }

        $otherActiveAdmins = $this->userRepository->findByConditions([
            ['role', '=', $user->role],
            ['status', '=', UserStatus::Active->value],
            ['id', '!=', $user->id]
        ]);

        if ($otherActiveAdmins->isEmpty()) {
            throw new BusinessException('Không thể khóa hoặc hạ quyền quản trị viên duy nhất của hệ thống.', 'last_admin');
        }
    }
}
