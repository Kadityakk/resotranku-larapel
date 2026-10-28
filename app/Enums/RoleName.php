<?php

namespace App\Enums;

/**
 * Roles the application ships with and gates behaviour on. Roles are editable
 * rows in the `roles` table, so these names are matched case-insensitively.
 */
enum RoleName: string
{
    case Admin = 'admin';
    case Cashier = 'cashier';
    case Chef = 'chef';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Cashier => 'Kasir',
            self::Chef => 'Koki',
            self::User => 'Pelanggan',
        };
    }

    /**
     * Route name each role lands on after signing in.
     */
    public function homeRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Cashier => 'cashier.orders.index',
            self::Chef => 'chef.orders.index',
            self::User => 'menu',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function staffValues(): array
    {
        return [self::Admin->value, self::Cashier->value, self::Chef->value];
    }
}
