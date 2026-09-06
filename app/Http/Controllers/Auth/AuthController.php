<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Validators\Auth\AuthValidator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected AuthService $authService;

    protected AuthValidator $authValidator;

    /**
     * @param AuthService $authService
     * @param AuthValidator $authValidator
     */
    public function __construct(AuthService $authService, AuthValidator $authValidator)
    {
        $this->authService = $authService;
        $this->authValidator = $authValidator;
    }

    /**
     * @return View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function login(Request $request)
    {
        // 1. Chỉ lấy field cần
        $data = $request->only(['login', 'password']);

        // 2. Validate dữ liệu
        $validator = $this->authValidator->loginValidator($data);
        if ($validator->fails()) {
            return back()->withErrors($validator)->onlyInput('login');
        }

        // 3. Gọi Service xử lý
        $result = $this->authService->login($data['login'], $data['password'], $request->boolean('remember'));

        // 4. Phản hồi
        if (!$result['status']) {
            return back()->withErrors(['login' => $result['message']])->onlyInput('login');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('success', $result['message']);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
