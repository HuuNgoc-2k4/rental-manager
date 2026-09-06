<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $this->get('/login')->assertOk();
});

test('user can login with email', function () {
    $user = User::factory()->owner()->create(['email' => 'owner@test.local']);

    $this->post('/login', ['login' => 'owner@test.local', 'password' => 'password'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('user can login with phone number', function () {
    $user = User::factory()->member()->create(['phone' => '0912345678']);

    $this->post('/login', ['login' => '0912345678', 'password' => 'password'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('login fails with wrong password', function () {
    User::factory()->owner()->create(['email' => 'owner@test.local']);

    $this->post('/login', ['login' => 'owner@test.local', 'password' => 'wrong-password'])
        ->assertSessionHasErrors('login');

    $this->assertGuest();
});

test('blocked user cannot login', function () {
    User::factory()->owner()->blocked()->create(['email' => 'blocked@test.local']);

    $this->post('/login', ['login' => 'blocked@test.local', 'password' => 'password'])
        ->assertSessionHasErrors('login');

    $this->assertGuest();
});

test('pending user only sees the waiting screen', function () {
    $user = User::factory()->owner()->pending()->create();

    $this->actingAs($user)->get('/')->assertOk()->assertViewIs('auth.pending');
});

test('user can logout', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));

    $this->assertGuest();
});
