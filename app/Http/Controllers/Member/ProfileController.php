<?php

namespace App\Http\Controllers\Member;

use App\Enums\Tenant\Gender;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Manage\TenantService;
use App\Services\Member\HouseholdService;
use App\Validators\Member\DeclarationValidator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    private const FIELDS = [
        'full_name', 'email', 'date_of_birth', 'gender', 'id_number',
        'id_issued_date', 'id_issued_place', 'permanent_address', 'occupation'
    ];

    protected HouseholdService $householdService;

    protected DeclarationValidator $declarationValidator;

    /**
     * @param HouseholdService $householdService
     * @param DeclarationValidator $declarationValidator
     */
    public function __construct(HouseholdService $householdService, DeclarationValidator $declarationValidator)
    {
        $this->householdService = $householdService;
        $this->declarationValidator = $declarationValidator;
    }

    /**
     * Hồ sơ của chính mình.
     *
     * @return View
     */
    public function edit()
    {
        return $this->form($this->householdService->getOwnProfile(Auth::user()), route('member.profile.update'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request)
    {
        $tenant = $this->householdService->getOwnProfile(Auth::user());
        abort_if($tenant === null, 404);

        return $this->save($request, $tenant, fn ($data, $files) => $this->householdService->declareOwn($tenant, $data, $files));
    }

    /**
     * Chủ hộ mở form khai hộ thành viên (chỉ người chưa có tài khoản, chưa xác minh, cùng hộ).
     *
     * @param Tenant $tenant
     * @return View
     */
    public function editMember(Tenant $tenant)
    {
        $this->householdService->assertCanDeclareForMember(Auth::user(), $tenant);

        return $this->form($tenant, route('member.household.members.update', $tenant), true);
    }

    /**
     * @param Request $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function updateMember(Request $request, Tenant $tenant)
    {
        $this->householdService->assertCanDeclareForMember(Auth::user(), $tenant);

        return $this->save($request, $tenant, fn ($data, $files) => $this->householdService->declareForMember(Auth::user(), $tenant, $data, $files));
    }

    /**
     * @param Tenant|null $tenant
     * @param string $action
     * @param bool $onBehalf
     * @return View
     */
    private function form(?Tenant $tenant, string $action, bool $onBehalf = false)
    {
        $genders = Gender::cases();
        $lockedFields = $tenant?->isVerified() ? TenantService::LOCKED_FIELDS : [];

        return view('member.profile', compact('tenant', 'action', 'onBehalf', 'genders', 'lockedFields'));
    }

    /**
     * @param Request $request
     * @param Tenant $tenant
     * @param callable $declare
     * @return RedirectResponse
     */
    private function save(Request $request, Tenant $tenant, callable $declare): RedirectResponse
    {
        // 1. Chỉ lấy field cần
        $data = $request->only(self::FIELDS);
        $files = $request->only(['id_front', 'id_back']);

        // 2. Validate dữ liệu
        $validator = $this->declarationValidator->submitValidator($data + $files + $request->only(['consent']), $tenant);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 3. Gọi Service xử lý
        $result = $declare($data, $files);

        // 4. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }
}
