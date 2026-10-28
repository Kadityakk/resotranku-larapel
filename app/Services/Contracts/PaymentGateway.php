<?php

namespace App\Services\Contracts;

use App\Models\Order;
use App\Services\PaymentNotification;

interface PaymentGateway
{
    /**
     * Opens a payment session and returns the token the browser needs to
     * display the gateway's payment popup.
     */
    public function createPaymentToken(Order $order): string;

    /**
     * Reads and verifies an incoming webhook payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function readNotification(array $payload): PaymentNotification;

    /**
     * Public key the front-end needs to initialise the gateway's script.
     */
    public function clientKey(): ?string;
}
