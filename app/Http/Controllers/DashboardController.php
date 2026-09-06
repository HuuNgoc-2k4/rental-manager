<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Thành viên (khách thuê) xem trang riêng, admin/chủ hộ xem trang quản lý.
     *
     * @return View
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isMember()) {
            return view('member.dashboard', compact('user'));
        }

        return view('dashboard.index', compact('user'));
    }
}
