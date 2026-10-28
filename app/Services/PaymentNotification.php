<?php

namespace App\Services;

use App\Enums\PaymentStatus;

/**
 * Gateway-agnostic reading of a payment webhook.
 */
readonly class PaymentNotification
{
    public function __construct(
        public string $orderCode,
        public PaymentStatus $paymentStatus,
        public ?string $transactionId = null,
        public ?string $rawStatus = null,
    ) {}

    public function isPaid(): bool
    {
        return $this->paymentStatus === PaymentStatus::Paid;
    }
}
