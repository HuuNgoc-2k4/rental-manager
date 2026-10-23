<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Member\HouseholdService;
use App\Validators\Manage\TenantValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HouseholdController extends Controller
{
    /** Chủ hộ khai hộ: không có ghi chú nội bộ của chủ nhà. */
    private const FIELDS = [
        'full_name', 'phone', 'email', 'date_of_birth', 'gender', 'id_number',
        'id_issued_date', 'id_issued_place', 'permanent_address', 'occupation'
    ];

    protected HouseholdService $householdService;

    protected TenantValidator $tenantValidator;

    /**
     * @param HouseholdService $householdService
     * @param TenantValidator $tenantValidator
     */
    public function __construct(HouseholdService $householdService, TenantValidator $tenantValidator)
    {
        $this->householdService = $householdService;
        $this->tenantValidator = $tenantValidator;
    }

    /**
     * Chủ hộ thêm người ở cùng (chờ chủ nhà duyệt).
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function storeMember(Request $request)
    {
        $data = $request->only(self::FIELDS);
        $files = $request->only(['id_front', 'id_back']);

        // 1. Validate dữ liệu
        $validator = $this->tenantValidator->saveValidator($data + $files);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 2. Gọi Service xử lý và phản hồi
        return $this->respond($this->householdService->addMember(Auth::user(), $data, $files));
    }

    /**
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function issueAccount(Tenant $tenant)
    {
        // 1. Gọi Service xử lý và phản hồi
        return $this->respond($this->householdService->issueMemberAccount(Auth::user(), $tenant));
    }

    /**
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function resetPassword(Tenant $tenant)
    {
        // 1. Gọi Service xử lý và phản hồi
        return $this->respond($this->householdService->resetMemberPassword(Auth::user(), $tenant));
    }

    /**
     * @param array $result
     * @return RedirectResponse
     */
    private function respond(array $result): RedirectResponse
    {
        if (!$result['status']) {
            return back()->with('error', $result['message'])->withInput();
        }

        $response = back()->with('success', $result['message']);

        return isset($result['credentials']) ? $response->with('credentials', $result['credentials']) : $response;
    }
}
