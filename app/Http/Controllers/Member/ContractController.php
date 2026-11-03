<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Services\Member\HouseholdService;
use App\Validators\Member\ContractReviewValidator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller
{
    /**
     * @param HouseholdService $householdService
     * @param ContractReviewValidator $reviewValidator
     */
    public function __construct(
        protected HouseholdService $householdService,
        protected ContractReviewValidator $reviewValidator
    ) {}

    /**
     * Hợp đồng thuê của hộ mình.
     *
     * @return View
     */
    public function show()
    {
        $user = Auth::user();
        $document = $this->householdService->getContractDocument($user);
        $household = $this->householdService->getPortal($user);
        $isOwner = (bool) ($household['isOwner'] ?? false);

        return view('member.contract', compact('document', 'household', 'isOwner'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function approve(Request $request)
    {
        // 1. Gọi Service xử lý
        $result = $this->householdService->approveContract(Auth::user(), $request->input('version'));

        // 2. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function reject(Request $request)
    {
        $data = $request->only(['reject_reason', 'version']);

        // 1. Validate dữ liệu
        $validator = $this->reviewValidator->rejectValidator($data);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 2. Gọi Service xử lý
        $result = $this->householdService->rejectContract(Auth::user(), $data['reject_reason'], $data['version'] ?? null);

        // 3. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }
}
