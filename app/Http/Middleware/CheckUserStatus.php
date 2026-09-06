<?php

namespace App\Http\Middleware;

use App\Enums\User\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Chặn tài khoản bị khóa (đăng xuất ngay) và tài khoản chờ duyệt (chỉ được xem trang chờ).
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $status = Auth::user()->status;

        if (UserStatus::isBlocked($status)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'login' => 'Tài khoản đã bị khóa, vui lòng liên hệ quản trị viên.'
            ]);
        }

        if (UserStatus::isPending($status) && !$request->routeIs('logout')) {
            return response()->view('auth.pending');
        }

        return $next($request);
    }
}
