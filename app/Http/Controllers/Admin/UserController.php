<?php

namespace App\Http\Controllers\Admin;

use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\UserService;
use App\Validators\Admin\UserValidator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    protected UserValidator $userValidator;

    /**
     * @param UserService $userService
     * @param UserValidator $userValidator
     */
    public function __construct(UserService $userService, UserValidator $userValidator)
    {
        $this->userService = $userService;
        $this->userValidator = $userValidator;
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $users = $this->userService->getList($request);
        $pendingUsers = $this->userService->getPendingUsers();
        $roles = UserRole::cases();
        $statuses = UserStatus::cases();

        return view('admin.users.index', compact('users', 'pendingUsers', 'roles', 'statuses'));
    }

    /**
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        $data = $request->only(['name', 'email', 'phone', 'password', 'role', 'status']);

        $validator = $this->userValidator->updateValidator($data, $user);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $result = $this->userService->update($user, $data);

        if (!$result['status']) {
            return back()->with('error', $result['message'])->withInput();
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Duyệt / khóa / mở lại tài khoản.
     *
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function updateStatus(Request $request, User $user)
    {
        $data = $request->only(['status']);

        $validator = $this->userValidator->updateStatusValidator($data);
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $result = $this->userService->updateStatus($user, $data['status']);

        if (!$result['status']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }
}
