<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Contracts\PaymentGateway;
use Illuminate\Validation\ValidationException;
use Midtrans\Config;
use Midtrans\Snap;
use RuntimeException;

class MidtransGateway implements PaymentGateway
{
    /**
     * @param  array{merchant_id: ?string, client_key: ?string, server_key: ?string, is_production: bool, is_sanitized: bool, is_3ds: bool}  $config
     */
    public function __construct(private readonly array $config) {}

    public function clientKey(): ?string
    {
        return $this->config['client_key'];
    }

    public function isConfigured(): bool
    {
        return filled($this->config['server_key']) && filled($this->config['client_key']);
    }

    public function createPaymentToken(Order $order): string
    {
        $this->applyConfig();

        $order->loadMissing('orderItems.item');

        return Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => $order->order_code,
                'gross_amount' => $order->grand_total,
            ],
            'item_details' => $this->itemDetails($order),
            'customer_details' => [
                'first_name' => $order->customer_name,
                'phone' => $order->customer_phone,
            ],
            'enabled_payments' => config('midtrans.enabled_payments'),
        ]);
    }

    public function readNotification(array $payload): PaymentNotification
    {
        $orderCode = (string) ($payload['order_id'] ?? '');
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? 'accept');

        if ($orderCode === '' || $transactionStatus === '') {
            throw ValidationException::withMessages([
                'order_id' => 'Notifikasi Midtrans tidak lengkap.',
            ]);
        }

        $this->assertSignatureIsValid($payload);

        return new PaymentNotification(
            orderCode: $orderCode,
            paymentStatus: $this->mapStatus($transactionStatus, $fraudStatus),
            transactionId: $payload['transaction_id'] ?? null,
            rawStatus: $transactionStatus,
        );
    }

    /**
     * Midtrans signs every notification with sha512 over the order id, status
     * code, gross amount and the merchant's server key.
     *
     * @param  array<string, mixed>  $payload
     */
    private function assertSignatureIsValid(array $payload): void
    {
        $expected = hash('sha512', implode('', [
            (string) ($payload['order_id'] ?? ''),
            (string) ($payload['status_code'] ?? ''),
            (string) ($payload['gross_amount'] ?? ''),
            (string) $this->config['server_key'],
        ]));

        if (! hash_equals($expected, (string) ($payload['signature_key'] ?? ''))) {
            throw ValidationException::withMessages([
                'signature_key' => 'Tanda tangan notifikasi Midtrans tidak valid.',
            ]);
        }
    }

    private function mapStatus(string $transactionStatus, string $fraudStatus): PaymentStatus
    {
        return match ($transactionStatus) {
            'capture' => $fraudStatus === 'challenge' ? PaymentStatus::Unpaid : PaymentStatus::Paid,
            'settlement' => PaymentStatus::Paid,
            'pending' => PaymentStatus::Unpaid,
            'deny', 'cancel' => PaymentStatus::Failed,
            'expire' => PaymentStatus::Expired,
            default => PaymentStatus::Unpaid,
        };
    }

    /**
     * Midtrans validates that item details sum to the gross amount, so tax is
     * sent as its own line rather than folded into each item's price.
     *
     * @return array<int, array<string, mixed>>
     */
    private function itemDetails(Order $order): array
    {
        $details = $order->orderItems
            ->map(fn ($orderItem) => [
                'id' => (string) $orderItem->item_id,
                'price' => $orderItem->price,
                'quantity' => $orderItem->quantity,
                'name' => str($orderItem->item?->name ?? 'Menu')->limit(50, '')->value(),
            ])
            ->all();

        if ($order->tax > 0) {
            $details[] = [
                'id' => 'TAX',
                'price' => $order->tax,
                'quantity' => 1,
                'name' => 'Pajak ('.(int) round($order->subtotal > 0 ? $order->tax / $order->subtotal * 100 : 0).'%)',
            ];
        }

        return $details;
    }

    private function applyConfig(): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Kredensial Midtrans belum diatur. Lengkapi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY pada file .env.');
        }

        Config::$serverKey = $this->config['server_key'];
        Config::$clientKey = $this->config['client_key'];
        Config::$isProduction = $this->config['is_production'];
        Config::$isSanitized = $this->config['is_sanitized'];
        Config::$is3ds = $this->config['is_3ds'];
    }
}
