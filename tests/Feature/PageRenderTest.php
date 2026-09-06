<?php

use App\Models\User;

test('every implemented page renders', function () {
    $this->get('/login')->assertOk()->assertSee('Đăng nhập', false);
    $this->get('/register')->assertOk()->assertSee('Đăng ký tài khoản chủ hộ', false);

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/')->assertOk()->assertSee('Tổng quan', false);
    $this->actingAs($admin)->get(route('admin.users.index'))->assertOk()->assertSee('Thêm tài khoản', false);

    $member = User::factory()->member()->create();
    $this->actingAs($member)->get('/')->assertOk()->assertSee('Phòng của tôi', false);

    $pending = User::factory()->owner()->pending()->create();
    $this->actingAs($pending)->get('/')->assertOk()->assertSee('Tài khoản đang chờ duyệt', false);
});
