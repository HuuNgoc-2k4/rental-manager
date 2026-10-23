<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Validators\Auth\PasswordValidator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PasswordController extends Controller
{
    protected AuthService $authService;

    protected PasswordValidator $passwordValidator;

    /**
     * @param AuthService $authService
     * @param PasswordValidator $passwordValidator
     */
    public function __construct(AuthService $authService, PasswordValidator $passwordValidator)
    {
        $this->authService = $authService;
        $this->passwordValidator = $passwordValidator;
    }

    /**
     * @return View
     */
    public function edit()
    {
        return view('auth.change-password', ['forced' => Auth::user()->must_change_password]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request)
    {
        $data = $request->only(['current_password', 'password', 'password_confirmation']);

        // 1. Validate dữ liệu
        $validator = $this->passwordValidator->updateValidator($data);
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // 2. Gọi Service xử lý
        $result = $this->authService->changePassword(Auth::user(), $data['password']);

        if (!$result['status']) {
            return back()->with('error', $result['message']);
        }

        // 3. Phản hồi
        return redirect()->route('dashboard')->with('success', $result['message']);
    }
}
