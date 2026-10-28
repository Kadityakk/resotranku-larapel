<?php

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->cashierRole = Role::named(RoleName::Cashier)->first()
        ?? Role::create(['role_name' => RoleName::Cashier->value, 'description' => 'Kasir']);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function employeePayload(Role $role, array $overrides = []): array
{
    return [
        'fullname' => 'Siti Nurhaliza',
        'username' => 'siti_kasir',
        'email' => 'siti@restoranku.test',
        'phone' => '081298765432',
        'role_id' => $role->id,
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        ...$overrides,
    ];
}

it('lists employees with their roles', function () {
    $cashier = User::factory()->cashier()->create(['fullname' => 'Kasir Satu']);

    $this->actingAs($this->admin)
        ->get(route('admin.employees.index'))
        ->assertOk()
        ->assertSee('Kasir Satu')
        ->assertSee($cashier->username);
});

it('stores a new employee with a hashed password', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.employees.store'), employeePayload($this->cashierRole))
        ->assertRedirect(route('admin.employees.index'))
        ->assertSessionHas('success');

    $employee = User::where('username', 'siti_kasir')->sole();

    expect($employee->fullname)->toBe('Siti Nurhaliza')
        ->and($employee->role_id)->toBe($this->cashierRole->id)
        ->and($employee->password)->not->toBe('Password123!')
        ->and(Hash::check('Password123!', $employee->password))->toBeTrue();
});

it('lets the new employee sign in straight away', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.employees.store'), employeePayload($this->cashierRole));

    auth()->logout();

    $this->post('/login', ['login' => 'siti_kasir', 'password' => 'Password123!'])
        ->assertRedirect(route('cashier.orders.index'));

    $this->assertAuthenticated();
});

it('validates the employee form', function (array $payload, string $field) {
    $this->actingAs($this->admin)
        ->post(route('admin.employees.store'), employeePayload($this->cashierRole, $payload))
        ->assertSessionHasErrors($field);
})->with([
    'missing fullname' => [['fullname' => ''], 'fullname'],
    'missing username' => [['username' => ''], 'username'],
    'username with spaces' => [['username' => 'siti kasir'], 'username'],
    'invalid email' => [['email' => 'bukan-email'], 'email'],
    'phone with letters' => [['phone' => 'nol delapan'], 'phone'],
    'unknown role' => [['role_id' => 9999], 'role_id'],
    'missing password' => [['password' => '', 'password_confirmation' => ''], 'password'],
    'password mismatch' => [['password_confirmation' => 'Berbeda123!'], 'password'],
]);

it('rejects a username that is already taken', function () {
    User::factory()->create(['username' => 'siti_kasir']);

    $this->actingAs($this->admin)
        ->post(route('admin.employees.store'), employeePayload($this->cashierRole))
        ->assertSessionHasErrors('username');
});

it('rejects an email that is already taken', function () {
    User::factory()->create(['email' => 'siti@restoranku.test']);

    $this->actingAs($this->admin)
        ->post(route('admin.employees.store'), employeePayload($this->cashierRole))
        ->assertSessionHasErrors('email');
});

it('updates an employee without touching the password when it is left blank', function () {
    $employee = User::factory()->cashier()->create();
    $originalPassword = $employee->password;

    $this->actingAs($this->admin)
        ->put(route('admin.employees.update', $employee), employeePayload($this->cashierRole, [
            'username' => $employee->username,
            'email' => $employee->email,
            'fullname' => 'Nama Diperbarui',
            'password' => '',
            'password_confirmation' => '',
        ]))
        ->assertRedirect(route('admin.employees.index'))
        ->assertSessionHasNoErrors();

    $employee->refresh();

    expect($employee->fullname)->toBe('Nama Diperbarui')
        ->and($employee->password)->toBe($originalPassword);
});

it('changes the password when a new one is supplied', function () {
    $employee = User::factory()->cashier()->create();

    $this->actingAs($this->admin)
        ->put(route('admin.employees.update', $employee), employeePayload($this->cashierRole, [
            'username' => $employee->username,
            'email' => $employee->email,
            'password' => 'PasswordBaru123!',
            'password_confirmation' => 'PasswordBaru123!',
        ]))
        ->assertSessionHasNoErrors();

    expect(Hash::check('PasswordBaru123!', $employee->refresh()->password))->toBeTrue();
});

it('can promote an employee to another role', function () {
    $employee = User::factory()->cashier()->create();
    $chefRole = Role::named(RoleName::Chef)->first()
        ?? Role::create(['role_name' => RoleName::Chef->value, 'description' => 'Koki']);

    $this->actingAs($this->admin)
        ->put(route('admin.employees.update', $employee), employeePayload($chefRole, [
            'username' => $employee->username,
            'email' => $employee->email,
        ]))
        ->assertSessionHasNoErrors();

    expect($employee->refresh()->isChef())->toBeTrue();
});

it('deletes an employee', function () {
    $employee = User::factory()->cashier()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.employees.destroy', $employee))
        ->assertRedirect(route('admin.employees.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($employee);
});

it('stops an admin from deleting their own account from here', function () {
    $this->actingAs($this->admin)
        ->delete(route('admin.employees.destroy', $this->admin))
        ->assertSessionHas('error');

    $this->assertNotSoftDeleted($this->admin);
});

it('shows a single employee', function () {
    $employee = User::factory()->chef()->create(['fullname' => 'Koki Hebat']);

    $this->actingAs($this->admin)
        ->get(route('admin.employees.show', $employee))
        ->assertOk()
        ->assertSee('Koki Hebat')
        ->assertSee($employee->email);
});

it('refuses employee management to a non-admin', function () {
    $cashier = User::factory()->cashier()->create();

    $this->actingAs($cashier)
        ->post(route('admin.employees.store'), employeePayload($this->cashierRole))
        ->assertForbidden();
});
