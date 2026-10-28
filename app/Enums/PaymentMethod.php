<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Qris = 'qris';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Tunai',
            self::Qris => 'QRIS / Non-Tunai',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Cash => 'fa-money-bill-wave',
            self::Qris => 'fa-qrcode',
        };
    }

    public function isOnline(): bool
    {
        return $this === self::Qris;
    }
}
