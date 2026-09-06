<?php

use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use App\Models\User;

test('owner can register and lands in pending state', function () {
    $this->post('/register', [
        'name' => 'Chủ hộ mới',
        'email' => 'newowner@test.local',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password'
    ])->assertRedirect(route('login'));

    $user = User::query()->where('email', 'newowner@test.local')->firstOrFail();

    expect($user->role)->toBe(UserRole::Owner->value)
        ->and($user->status)->toBe(UserStatus::Pending->value);
});

test('register requires an email or a phone number', function () {
    $this->post('/register', [
        'name' => 'Không liên hệ',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password'
    ])->assertSessionHasErrors(['email', 'phone']);

    expect(User::query()->count())->toBe(0);
});
