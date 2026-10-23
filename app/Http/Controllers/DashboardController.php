<?php

namespace App\Http\Controllers;

use App\Services\Manage\ApprovalService;
use App\Services\Manage\PropertyService;
use App\Services\Member\HouseholdService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * @param HouseholdService $householdService
     * @param PropertyService $propertyService
     * @param ApprovalService $approvalService
     */
    public function __construct(
        protected HouseholdService $householdService,
        protected PropertyService $propertyService,
        protected ApprovalService $approvalService
    ) {}

    /**
     * Chủ hộ và thành viên (người thuê) xem "Phòng của tôi", admin (chủ nhà) xem trang quản lý.
     *
     * @return View
     */
    public function index(): View
    {
        $user = Auth::user();

        // 1. Người thuê: cổng thành viên
        if (!$user->isAdmin()) {
            return view('member.dashboard', [
                'user' => $user,
                'household' => $this->householdService->getPortal($user),
                'profile' => $this->householdService->getOwnProfile($user)
            ]);
        }

        // 2. Chủ nhà: số liệu theo khu + việc cần duyệt
        return view('dashboard.index', [
            'user' => $user,
            'properties' => $this->propertyService->getList(),
            'pendingMembers' => $this->approvalService->getPendingMembers(),
            'declarations' => $this->approvalService->getSubmittedDeclarations()
        ]);
    }
}
