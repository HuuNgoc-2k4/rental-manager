<?php

namespace App\Http\Controllers;

use App\Services\Member\HouseholdService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * @param HouseholdService $householdService
     */
    public function __construct(protected HouseholdService $householdService) {}

    /**
     * Chủ hộ và thành viên (người thuê) xem "Phòng của tôi", admin (chủ nhà) xem trang quản lý.
     *
     * @return View
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return view('member.dashboard', ['user' => $user, 'household' => $this->householdService->getPortal($user)]);
        }

        return view('dashboard.index', compact('user'));
    }
}
