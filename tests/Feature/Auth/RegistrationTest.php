<?php

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RoleSeeder;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $this->seed(RoleSeeder::class);

    $response = $this->post('/register', [
        'fullname' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'phone' => '081234567890',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('menu'));

    expect(User::where('username', 'testuser')->first())
        ->not->toBeNull()
        ->fullname->toBe('Test User');
});

test('self registration always creates a customer, never staff', function () {
    $this->seed(RoleSeeder::class);

    $this->post('/register', [
        'fullname' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'phone' => '081234567890',
        'role_id' => 1,
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $user = User::where('username', 'testuser')->firstOrFail();

    expect($user->roleName())->toBe(RoleName::User)
        ->and($user->isStaff())->toBeFalse();
});

test('registration rejects a duplicate username', function () {
    $this->seed(RoleSeeder::class);
    User::factory()->create(['username' => 'testuser']);

    $response = $this->post('/register', [
        'fullname' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'phone' => '081234567890',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSessionHasErrors('username');
    $this->assertGuest();
});
