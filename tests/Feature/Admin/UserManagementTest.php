<?php

use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use App\Models\User;

test('admin can view the user list', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
});

test('owner and member cannot reach user management', function (string $state) {
    $user = User::factory()->{$state}()->create();

    $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
})->with(['owner', 'member']);

test('admin can create an account', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Khách thuê C',
        'phone' => '0987654321',
        'password' => 'secret-password',
        'role' => UserRole::Member->value,
        'status' => UserStatus::Active->value
    ])->assertRedirect();

    expect(User::query()->where('phone', '0987654321')->exists())->toBeTrue();
});

test('admin can approve a pending owner', function () {
    $admin = User::factory()->admin()->create();
    $pending = User::factory()->owner()->pending()->create();

    $this->actingAs($admin)
        ->patch(route('admin.users.status', $pending), ['status' => UserStatus::Active->value])
        ->assertRedirect();

    expect($pending->fresh()->status)->toBe(UserStatus::Active->value);
});

test('admin can update an account without changing its password', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $originalPassword = $member->password;

    $this->actingAs($admin)->put(route('admin.users.update', $member), [
        'name' => 'Tên đã sửa',
        'email' => $member->email,
        'phone' => $member->phone,
        'password' => '',
        'role' => UserRole::Member->value,
        'status' => UserStatus::Active->value
    ])->assertRedirect();

    $member->refresh();

    expect($member->name)->toBe('Tên đã sửa')
        ->and($member->password)->toBe($originalPassword);
});

test('the last active admin cannot be blocked', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->patch(route('admin.users.status', $admin), ['status' => UserStatus::Blocked->value])
        ->assertSessionHas('error');

    expect($admin->fresh()->status)->toBe(UserStatus::Active->value);
});

test('an admin can be blocked when another active admin remains', function () {
    $admin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->patch(route('admin.users.status', $otherAdmin), ['status' => UserStatus::Blocked->value])
        ->assertSessionHas('success');

    expect($otherAdmin->fresh()->status)->toBe(UserStatus::Blocked->value);
});
