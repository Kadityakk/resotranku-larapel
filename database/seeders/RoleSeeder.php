<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seeds the four roles the application gates behaviour on. Safe to re-run.
     */
    public function run(): void
    {
        $descriptions = [
            RoleName::Admin->value => 'Administrator dengan akses penuh ke seluruh menu pengelolaan',
            RoleName::Cashier->value => 'Kasir yang menerima pembayaran dan mengonfirmasi pesanan',
            RoleName::Chef->value => 'Koki yang memasak dan memperbarui status pesanan di dapur',
            RoleName::User->value => 'Pelanggan yang memesan menu dari meja',
        ];

        foreach ($descriptions as $roleName => $description) {
            Role::updateOrCreate(
                ['role_name' => $roleName],
                ['description' => $description],
            );
        }
    }
}
