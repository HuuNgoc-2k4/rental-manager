<?php

namespace Database\Seeders;

use App\Enums\Contract\ContractStatus;
use App\Enums\Contract\MemberApproval;
use App\Enums\Property\RoomStatus;
use App\Enums\Tenant\TenantInfoStatus;
use App\Models\Contract;
use App\Models\ContractTenant;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class HouseholdSeeder extends Seeder
{
    /**
     * 3 hộ đang ở, dùng lại tài khoản mẫu của UserSeeder:
     * - Phòng 101: chủ hộ owner@rental.test + thành viên member@rental.test (đã xác minh)
     * - Phòng 102: chủ hộ chưa có tài khoản, 1 thành viên chờ chủ nhà duyệt
     * - Phòng 201: chủ hộ chưa có tài khoản, đã khai báo và chờ duyệt thông tin
     */
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@rental.test')->firstOrFail();
        $owner = User::query()->where('email', 'owner@rental.test')->firstOrFail();
        $member = User::query()->where('email', 'member@rental.test')->firstOrFail();
        $propertyId = Property::query()->where('name', 'Toà A')->value('id');

        $household1 = $this->rentOut($propertyId, '101', $this->tenantFor($owner, '012345678001'), $admin);
        if ($household1) {
            $this->addMember($household1, $this->tenantFor($member, '012345678002'), $admin, MemberApproval::Approved);
        }

        $household2 = $this->rentOut($propertyId, '102', $this->tenant('Lê Văn Cường', '0911000102', '012345678003'), $admin);
        if ($household2) {
            $this->addMember($household2, $this->tenant('Lê Thị Dung', '0911000103', null), $admin, MemberApproval::Pending);
        }

        $this->rentOut($propertyId, '201', $this->tenant('Phạm Thị Hoa', '0911000201', '012345678004', TenantInfoStatus::Submitted), $admin);
    }

    /**
     * @param int $propertyId
     * @param string $roomName
     * @param Tenant $tenant
     * @param User $admin
     * @return Contract|null null nếu phòng đã có hợp đồng (chạy lại seeder)
     */
    private function rentOut(int $propertyId, string $roomName, Tenant $tenant, User $admin): ?Contract
    {
        $room = Room::query()->where('property_id', $propertyId)->where('name', $roomName)->firstOrFail();

        if (Contract::query()->where('room_id', $room->id)->exists()) {
            return null;
        }

        $contract = Contract::query()->create([
            'room_id' => $room->id,
            'start_date' => now()->subMonths(3)->toDateString(),
            'status' => ContractStatus::Active->value
        ]);
        $room->update(['status' => RoomStatus::Occupied->value]);

        $this->link($contract, $tenant, $admin, true, MemberApproval::Approved);

        return $contract;
    }

    /**
     * @param Contract $contract
     * @param Tenant $tenant
     * @param User $admin
     * @param MemberApproval $approval
     * @return void
     */
    private function addMember(Contract $contract, Tenant $tenant, User $admin, MemberApproval $approval): void
    {
        $this->link($contract, $tenant, $admin, false, $approval);
    }

    /**
     * @param Contract $contract
     * @param Tenant $tenant
     * @param User $createdBy
     * @param bool $isPrimary
     * @param MemberApproval $approval
     * @return void
     */
    private function link(Contract $contract, Tenant $tenant, User $createdBy, bool $isPrimary, MemberApproval $approval): void
    {
        ContractTenant::query()->create([
            'contract_id' => $contract->id,
            'tenant_id' => $tenant->id,
            'is_primary' => $isPrimary,
            'approval_status' => $approval->value,
            'joined_at' => $contract->start_date->toDateString(),
            'created_by' => $createdBy->id
        ]);
    }

    /**
     * Hồ sơ đã xác minh gắn với tài khoản mẫu.
     *
     * @param User $user
     * @param string $idNumber
     * @return Tenant
     */
    private function tenantFor(User $user, string $idNumber): Tenant
    {
        return Tenant::query()->updateOrCreate(['user_id' => $user->id], [
            'full_name' => $user->name,
            'phone' => $user->phone,
            'date_of_birth' => '1995-05-20',
            'id_number' => $idNumber,
            'permanent_address' => 'Hà Nội',
            'info_status' => TenantInfoStatus::Verified->value,
            'info_verified_at' => now()
        ]);
    }

    /**
     * @param string $name
     * @param string $phone
     * @param string|null $idNumber
     * @param TenantInfoStatus $status
     * @return Tenant
     */
    private function tenant(string $name, string $phone, ?string $idNumber, TenantInfoStatus $status = TenantInfoStatus::Draft): Tenant
    {
        return Tenant::query()->firstOrCreate(['phone' => $phone], [
            'full_name' => $name,
            'id_number' => $idNumber,
            'date_of_birth' => $idNumber ? '1992-03-10' : null,
            'permanent_address' => $idNumber ? 'Hà Nội' : null,
            'info_status' => $status->value
        ]);
    }
}
