<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seeds one account per role so every part of the back office can be signed
     * into straight after a fresh install. Safe to re-run.
     */
    public function run(): void
    {
        $accounts = [
            [
                'username' => 'admin',
                'fullname' => 'Admin Restoranku',
                'email' => 'admin@restoranku.test',
                'phone' => '081200000001',
                'role' => RoleName::Admin,
            ],
            [
                'username' => 'kasir',
                'fullname' => 'Kasir Restoranku',
                'email' => 'kasir@restoranku.test',
                'phone' => '081200000002',
                'role' => RoleName::Cashier,
            ],
            [
                'username' => 'koki',
                'fullname' => 'Koki Restoranku',
                'email' => 'koki@restoranku.test',
                'phone' => '081200000003',
                'role' => RoleName::Chef,
            ],
        ];

        foreach ($accounts as $account) {
            $roleId = Role::named($account['role'])->value('id');

            User::updateOrCreate(
                ['username' => $account['username']],
                [
                    'fullname' => $account['fullname'],
                    'email' => $account['email'],
                    'phone' => $account['phone'],
                    'role_id' => $roleId,
                    'password' => 'password',
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
