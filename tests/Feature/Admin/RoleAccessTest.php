<?php

use App\Models\User;

it('sends a guest to the login page', function (string $route) {
    $this->get(route($route))->assertRedirect(route('login'));
})->with([
    'admin.dashboard',
    'admin.items.index',
    'admin.categories.index',
    'admin.roles.index',
    'admin.employees.index',
    'admin.orders.index',
    'cashier.orders.index',
    'chef.orders.index',
]);

it('lets an admin reach every staff area', function (string $route) {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route($route))->assertOk();
})->with([
    'admin.dashboard',
    'admin.items.index',
    'admin.categories.index',
    'admin.roles.index',
    'admin.employees.index',
    'admin.orders.index',
    'cashier.orders.index',
    'chef.orders.index',
]);

it('keeps a cashier out of the admin area', function (string $route) {
    $cashier = User::factory()->cashier()->create();

    $this->actingAs($cashier)->get(route($route))->assertForbidden();
})->with([
    'admin.dashboard',
    'admin.items.index',
    'admin.employees.index',
    'admin.orders.index',
]);

it('lets a cashier reach the till but not the kitchen', function () {
    $cashier = User::factory()->cashier()->create();

    $this->actingAs($cashier)->get(route('cashier.orders.index'))->assertOk();
    $this->actingAs($cashier)->get(route('chef.orders.index'))->assertForbidden();
});

it('lets a chef reach the kitchen but not the till', function () {
    $chef = User::factory()->chef()->create();

    $this->actingAs($chef)->get(route('chef.orders.index'))->assertOk();
    $this->actingAs($chef)->get(route('cashier.orders.index'))->assertForbidden();
});

it('keeps a customer out of every staff area', function (string $route) {
    $customer = User::factory()->create();

    $this->actingAs($customer)->get(route($route))->assertForbidden();
})->with([
    'admin.dashboard',
    'cashier.orders.index',
    'chef.orders.index',
]);

it('points the generic dashboard route at the right place per role', function (string $state, string $route) {
    $user = User::factory()->{$state}()->create();

    $this->actingAs($user)->get('/dashboard')->assertRedirect(route($route));
})->with([
    'admin' => ['admin', 'admin.dashboard'],
    'cashier' => ['cashier', 'cashier.orders.index'],
    'chef' => ['chef', 'chef.orders.index'],
]);
