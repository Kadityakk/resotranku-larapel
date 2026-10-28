<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using their email address', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'login' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('menu'));
});

test('users can authenticate using their username', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'login' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('menu'));
});

test('staff land on the dashboard that matches their role', function (string $state, string $route) {
    $user = User::factory()->{$state}()->create();

    $response = $this->post('/login', [
        'login' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route($route));
})->with([
    'admin' => ['admin', 'admin.dashboard'],
    'cashier' => ['cashier', 'cashier.orders.index'],
    'chef' => ['chef', 'chef.orders.index'],
]);

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'login' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can not authenticate with an unknown login', function () {
    User::factory()->create();

    $this->post('/login', [
        'login' => 'tidak-ada',
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});
