<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\ContractTenant;
use App\Services\Manage\ApprovalService;
use App\Validators\Manage\ApprovalValidator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
     * @return View
     */
    public function index()
    {
        $pendingMembers = $this->approvalService->getPendingMembers();

        return view('manage.approvals.index', compact('pendingMembers'));
    }

    /**
     * @param ContractTenant $link
     * @return RedirectResponse
     */
    public function approveMember(ContractTenant $link)
    {
        $result = $this->approvalService->approveMember($link);

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

        $validator = $this->approvalValidator->rejectValidator($data);
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $result = $this->approvalService->rejectMember($link, $data['reason']);

        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }
}
