<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Failed = 'failed';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Belum Dibayar',
            self::Paid => 'Sudah Dibayar',
            self::Failed => 'Gagal',
            self::Expired => 'Kedaluwarsa',
        };
    }

    /**
     * Bootstrap contextual colour used for payment badges.
     */
    public function color(): string
    {
        return match ($this) {
            self::Unpaid => 'warning',
            self::Paid => 'success',
            self::Failed => 'danger',
            self::Expired => 'secondary',
        };
    }
}
