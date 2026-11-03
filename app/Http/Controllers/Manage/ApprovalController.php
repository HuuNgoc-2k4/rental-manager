<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\ContractTenant;
use App\Models\Tenant;
use App\Services\Manage\ApprovalService;
use App\Validators\Manage\ApprovalValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    protected ApprovalService $approvalService;

    protected ApprovalValidator $approvalValidator;

    /**
     * @param ApprovalService $approvalService
     * @param ApprovalValidator $approvalValidator
     */
    public function __construct(ApprovalService $approvalService, ApprovalValidator $approvalValidator)
    {
        $this->approvalService = $approvalService;
        $this->approvalValidator = $approvalValidator;
    }

    /**
     * @param ContractTenant $link
     * @return RedirectResponse
     */
    public function approveMember(ContractTenant $link)
    {
        // 1. Gọi Service xử lý
        $result = $this->approvalService->approveMember($link);

        // 2. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }

    /**
     * @param Request $request
     * @param ContractTenant $link
     * @return RedirectResponse
     */
    public function rejectMember(Request $request, ContractTenant $link)
    {
        $data = $request->only(['reason']);

        // 1. Validate dữ liệu
        $validator = $this->approvalValidator->rejectValidator($data);
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // 2. Gọi Service xử lý
        $result = $this->approvalService->rejectMember($link, $data['reason']);

        // 3. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }

    /**
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function verifyDeclaration(Tenant $tenant)
    {
        // 1. Gọi Service xử lý
        $result = $this->approvalService->verifyDeclaration($tenant, Auth::user());

        // 2. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }

    /**
     * @param Request $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function returnDeclaration(Request $request, Tenant $tenant)
    {
        $data = $request->only(['reason']);

        // 1. Validate dữ liệu
        $validator = $this->approvalValidator->rejectValidator($data);
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // 2. Gọi Service xử lý
        $result = $this->approvalService->returnDeclaration($tenant, $data['reason']);

        // 3. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }
}
