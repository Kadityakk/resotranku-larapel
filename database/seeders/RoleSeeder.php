<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'role_name' => 'Admin',
                'description' => 'Administrator',
            ],
            [
                'role_name' => 'cashier',
                'description' => 'Cashier',
            ],
            [
                'role_name' => 'Chef',
                'description' => 'Chef',
            ],

            [
                'role_name' => 'User',
                'description' => 'User',
            ],
        ];
        DB::table('roles')->insert($roles);
    }
}
