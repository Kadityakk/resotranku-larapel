<?php

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('lists roles with their user counts', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.roles.index'))
        ->assertOk()
        ->assertSee('admin')
        ->assertSee('1 pengguna');
});

it('stores a new custom role', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.roles.store'), [
            'role_name' => 'waiter',
            'description' => 'Pelayan yang mengantar pesanan ke meja',
        ])
        ->assertRedirect(route('admin.roles.index'))
        ->assertSessionHas('success');

    expect(Role::where('role_name', 'waiter')->exists())->toBeTrue();
});

it('rejects a duplicate role name', function () {
    Role::factory()->create(['role_name' => 'waiter']);

    $this->actingAs($this->admin)
        ->post(route('admin.roles.store'), [
            'role_name' => 'waiter',
            'description' => 'Duplikat',
        ])
        ->assertSessionHasErrors('role_name');
});

it('validates the role form', function (array $payload, string $field) {
    $this->actingAs($this->admin)
        ->post(route('admin.roles.store'), $payload)
        ->assertSessionHasErrors($field);
})->with([
    'missing name' => [['role_name' => '', 'description' => 'Ada'], 'role_name'],
    'missing description' => [['role_name' => 'baru', 'description' => ''], 'description'],
]);

it('updates a custom role', function () {
    $role = Role::factory()->create(['role_name' => 'waiter']);

    $this->actingAs($this->admin)
        ->put(route('admin.roles.update', $role), [
            'role_name' => 'pelayan',
            'description' => 'Deskripsi baru',
        ])
        ->assertRedirect(route('admin.roles.index'))
        ->assertSessionHasNoErrors();

    $role->refresh();

    expect($role->role_name)->toBe('pelayan')
        ->and($role->description)->toBe('Deskripsi baru');
});

it('will not let a built-in role be renamed, but allows its description to change', function () {
    $role = Role::named(RoleName::Admin)->sole();

    $this->actingAs($this->admin)
        ->put(route('admin.roles.update', $role), [
            'role_name' => 'superuser',
            'description' => 'Deskripsi diperbarui',
        ])
        ->assertRedirect(route('admin.roles.index'));

    $role->refresh();

    expect($role->role_name)->toBe(RoleName::Admin->value)
        ->and($role->description)->toBe('Deskripsi diperbarui');
});

it('refuses to delete a built-in role', function () {
    $role = Role::named(RoleName::Admin)->sole();

    $this->actingAs($this->admin)
        ->delete(route('admin.roles.destroy', $role))
        ->assertSessionHas('error');

    $this->assertNotSoftDeleted($role);
});

it('refuses to delete a role that still has users', function () {
    $role = Role::factory()->create(['role_name' => 'waiter']);
    User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($this->admin)
        ->delete(route('admin.roles.destroy', $role))
        ->assertSessionHas('error');

    $this->assertNotSoftDeleted($role);
});

it('deletes an unused custom role', function () {
    $role = Role::factory()->create(['role_name' => 'waiter']);

    $this->actingAs($this->admin)
        ->delete(route('admin.roles.destroy', $role))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($role);
});

it('matches a built-in role whatever its capitalisation', function () {
    Role::where('role_name', RoleName::Chef->value)->delete();
    Role::create(['role_name' => 'Chef', 'description' => 'Koki']);

    expect(Role::named(RoleName::Chef)->first()?->isBuiltIn())->toBeTrue();
});
