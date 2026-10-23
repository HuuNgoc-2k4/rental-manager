<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractTenant;
use App\Models\Room;
use App\Models\Tenant;
use App\Services\Manage\ContractService;
use App\Validators\Manage\ContractValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller
{
    protected ContractService $contractService;

    protected ContractValidator $contractValidator;

    /**
     * @param ContractService $contractService
     * @param ContractValidator $contractValidator
     */
    public function __construct(ContractService $contractService, ContractValidator $contractValidator)
    {
        $this->contractService = $contractService;
        $this->contractValidator = $contractValidator;
    }

    /**
     * Cho thuê phòng.
     *
     * @param Request $request
     * @param Room $room
     * @return RedirectResponse
     */
    public function store(Request $request, Room $room)
    {
        $data = $request->only(['tenant_id', 'full_name', 'phone', 'start_date', 'end_date', 'note']);
        $data['issue_account'] = $request->boolean('issue_account');

        // 1. Validate dữ liệu
        $validator = $this->contractValidator->rentOutValidator($data);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 2. Gọi Service xử lý và phản hồi
        return $this->respond($this->contractService->rentOut($room, $data, Auth::user()));
    }

    /**
     * Admin thêm người ở cùng (duyệt luôn).
     *
     * @param Request $request
     * @param Contract $contract
     * @return RedirectResponse
     */
    public function addMember(Request $request, Contract $contract)
    {
        $data = $request->only(['tenant_id', 'full_name', 'phone']);

        // 1. Validate dữ liệu
        $validator = $this->contractValidator->addMemberValidator($data);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 2. Gọi Service xử lý và phản hồi
        return $this->respond($this->contractService->addMember($contract, $data, Auth::user()));
    }

    /**
     * @param ContractTenant $link
     * @return RedirectResponse
     */
    public function leave(ContractTenant $link)
    {
        // 1. Gọi Service xử lý và phản hồi
        return $this->respond($this->contractService->leave($link));
    }

    /**
     * Trả phòng.
     *
     * @param Request $request
     * @param Contract $contract
     * @return RedirectResponse
     */
    public function checkout(Request $request, Contract $contract)
    {
        $data = $request->only(['ended_at']);

        // 1. Validate dữ liệu
        $validator = $this->contractValidator->checkoutValidator($data);
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // 2. Gọi Service xử lý và phản hồi
        return $this->respond($this->contractService->checkout($contract, $data['ended_at']));
    }

    /**
     * Cấp tài khoản cho người trong hộ.
     *
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function issueAccount(Tenant $tenant)
    {
        // 1. Gọi Service xử lý và phản hồi
        return $this->respond($this->contractService->issueAccount($tenant));
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
