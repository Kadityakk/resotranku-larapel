<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cooking = 'cooking';
    case Cooked = 'cooked';
    case Served = 'served';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Konfirmasi',
            self::Confirmed => 'Dikonfirmasi',
            self::Cooking => 'Sedang Dimasak',
            self::Cooked => 'Siap Disajikan',
            self::Served => 'Sudah Disajikan',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /**
     * Bootstrap contextual colour used for status badges.
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'info',
            self::Cooking => 'primary',
            self::Cooked => 'success',
            self::Served => 'secondary',
            self::Cancelled => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'fa-hourglass-half',
            self::Confirmed => 'fa-circle-check',
            self::Cooking => 'fa-fire-burner',
            self::Cooked => 'fa-bell-concierge',
            self::Served => 'fa-utensils',
            self::Cancelled => 'fa-circle-xmark',
        };
    }

    /**
     * Statuses a chef is allowed to move an order into.
     *
     * @return array<int, self>
     */
    public static function kitchenStatuses(): array
    {
        return [self::Cooking, self::Cooked, self::Served];
    }

    /**
     * Orders that still need work in the kitchen.
     *
     * @return array<int, string>
     */
    public static function activeKitchenValues(): array
    {
        return [self::Confirmed->value, self::Cooking->value, self::Cooked->value];
    }

    /**
     * Statuses this order may legally move into next. Cancelling is allowed from
     * any status that is not already final.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Cooking, self::Cancelled],
            self::Cooking => [self::Cooked, self::Cancelled],
            self::Cooked => [self::Served, self::Cancelled],
            self::Served, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Served, self::Cancelled], true);
    }
}
