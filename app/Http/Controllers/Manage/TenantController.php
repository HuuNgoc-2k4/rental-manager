<?php

namespace App\Http\Controllers\Manage;

use App\Enums\Tenant\Gender;
use App\Enums\Tenant\TenantInfoStatus;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Manage\TenantService;
use App\Validators\Manage\TenantValidator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    private const FIELDS = [
        'full_name', 'phone', 'email', 'date_of_birth', 'gender', 'id_number',
        'id_issued_date', 'id_issued_place', 'permanent_address', 'occupation', 'note'
    ];

    protected TenantService $tenantService;

    protected TenantValidator $tenantValidator;

    /**
     * @param TenantService $tenantService
     * @param TenantValidator $tenantValidator
     */
    public function __construct(TenantService $tenantService, TenantValidator $tenantValidator)
    {
        $this->tenantService = $tenantService;
        $this->tenantValidator = $tenantValidator;
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $tenants = $this->tenantService->getList($request);
        $genders = Gender::cases();
        $infoStatuses = TenantInfoStatus::cases();

        return view('manage.tenants.index', compact('tenants', 'genders', 'infoStatuses'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->only(self::FIELDS);
        $files = $request->only(['id_front', 'id_back']);

        // 1. Validate dữ liệu
        $validator = $this->tenantValidator->saveValidator($data + $files);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 2. Gọi Service xử lý
        $result = $this->tenantService->store($data, $files, Auth::user());

        // 3. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }

    /**
     * @param Request $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->only(self::FIELDS);
        $files = $request->only(['id_front', 'id_back']);

        // 1. Validate dữ liệu
        $validator = $this->tenantValidator->saveValidator($data + $files, $tenant);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 2. Gọi Service xử lý
        $result = $this->tenantService->update($tenant, $data, $files, Auth::user());

        // 3. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }

    /**
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function destroy(Tenant $tenant)
    {
        // 1. Gọi Service xử lý
        $result = $this->tenantService->delete($tenant);

        // 2. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }
}
