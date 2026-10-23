<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Chủ hộ và thành viên (người thuê) xem cổng hộ thuê, admin (chủ nhà) xem trang quản lý.
     *
     * @return View
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return view('member.dashboard', compact('user'));
        }

        return view('dashboard.index', compact('user'));
    }
}
