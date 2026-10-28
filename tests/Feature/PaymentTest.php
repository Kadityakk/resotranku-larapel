<?php

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Contracts\PaymentGateway;
use App\Services\MidtransGateway;
use App\Services\PaymentNotification;

/**
 * Signs a notification payload the way Midtrans does.
 *
 * @param  array<string, mixed>  $payload
 * @return array<string, mixed>
 */
function signMidtransPayload(array $payload, string $serverKey): array
{
    $payload['signature_key'] = hash('sha512', implode('', [
        $payload['order_id'],
        $payload['status_code'],
        $payload['gross_amount'],
        $serverKey,
    ]));

    return $payload;
}

function fakeMidtransCredentials(string $serverKey = 'SB-Mid-server-test'): void
{
    config()->set('midtrans.server_key', $serverKey);
    config()->set('midtrans.client_key', 'SB-Mid-client-test');
    config()->set('midtrans.is_production', false);
}

describe('snap payment page', function () {
    it('shows the snap popup for an unpaid online order', function () {
        $order = Order::factory()->online()->create();
        session()->put('recent_order_codes', [$order->order_code]);

        $this->mock(PaymentGateway::class, function ($mock) {
            $mock->shouldReceive('createPaymentToken')->once()->andReturn('snap-token-123');
            $mock->shouldReceive('clientKey')->andReturn('SB-Mid-client-test');
        });

        $this->get(route('payment.show', $order->order_code))
            ->assertOk()
            ->assertSee('snap-token-123', false)
            ->assertSee($order->order_code);

        expect($order->refresh()->snap_token)->toBe('snap-token-123');
    });

    it('reuses a snap token that was already issued', function () {
        $order = Order::factory()->online()->create(['snap_token' => 'existing-token']);
        session()->put('recent_order_codes', [$order->order_code]);

        $this->mock(PaymentGateway::class, function ($mock) {
            $mock->shouldReceive('createPaymentToken')->never();
            $mock->shouldReceive('clientKey')->andReturn('SB-Mid-client-test');
        });

        $this->get(route('payment.show', $order->order_code))
            ->assertOk()
            ->assertSee('existing-token', false);
    });

    it('sends an already paid order to the success page', function () {
        $order = Order::factory()->online()->paid()->create();
        session()->put('recent_order_codes', [$order->order_code]);

        $this->get(route('payment.show', $order->order_code))
            ->assertRedirect(route('checkout.success', $order->order_code));
    });

    it('falls back to paying at the till when the gateway is unavailable', function () {
        $order = Order::factory()->online()->create();
        session()->put('recent_order_codes', [$order->order_code]);

        $this->mock(PaymentGateway::class, function ($mock) {
            $mock->shouldReceive('createPaymentToken')->andThrow(new RuntimeException('Kredensial Midtrans belum diatur.'));
            $mock->shouldReceive('clientKey')->andReturn(null);
        });

        $this->get(route('payment.show', $order->order_code))
            ->assertRedirect(route('checkout.success', $order->order_code))
            ->assertSessionHas('error');
    });

    it('hides the payment page from another browser', function () {
        $order = Order::factory()->online()->create();

        $this->get(route('payment.show', $order->order_code))->assertNotFound();
    });
});

describe('midtrans webhook', function () {
    beforeEach(function () {
        fakeMidtransCredentials();
    });

    it('marks an order paid on settlement', function () {
        $order = Order::factory()->online()->create(['grand_total' => 55000]);

        $payload = signMidtransPayload([
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => '55000.00',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => 'trx-abc-123',
        ], 'SB-Mid-server-test');

        $this->postJson(route('payment.notification'), $payload)->assertOk();

        $order->refresh();

        expect($order->payment_status)->toBe(PaymentStatus::Paid)
            ->and($order->midtrans_transaction_id)->toBe('trx-abc-123')
            ->and($order->paid_at)->not->toBeNull();
    });

    it('maps every midtrans status onto a payment status', function (string $midtransStatus, PaymentStatus $expected) {
        $order = Order::factory()->online()->create(['grand_total' => 10000]);

        $payload = signMidtransPayload([
            'order_id' => $order->order_code,
            'status_code' => '201',
            'gross_amount' => '10000.00',
            'transaction_status' => $midtransStatus,
            'fraud_status' => 'accept',
        ], 'SB-Mid-server-test');

        $this->postJson(route('payment.notification'), $payload)->assertOk();

        expect($order->refresh()->payment_status)->toBe($expected);
    })->with([
        'capture' => ['capture', PaymentStatus::Paid],
        'settlement' => ['settlement', PaymentStatus::Paid],
        'pending' => ['pending', PaymentStatus::Unpaid],
        'deny' => ['deny', PaymentStatus::Failed],
        'cancel' => ['cancel', PaymentStatus::Failed],
        'expire' => ['expire', PaymentStatus::Expired],
    ]);

    it('leaves a fraud challenge unpaid', function () {
        $order = Order::factory()->online()->create(['grand_total' => 10000]);

        $payload = signMidtransPayload([
            'order_id' => $order->order_code,
            'status_code' => '201',
            'gross_amount' => '10000.00',
            'transaction_status' => 'capture',
            'fraud_status' => 'challenge',
        ], 'SB-Mid-server-test');

        $this->postJson(route('payment.notification'), $payload)->assertOk();

        expect($order->refresh()->payment_status)->toBe(PaymentStatus::Unpaid);
    });

    it('rejects a notification with a bad signature', function () {
        $order = Order::factory()->online()->create();

        $this->postJson(route('payment.notification'), [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => '55000.00',
            'transaction_status' => 'settlement',
            'signature_key' => 'tanda-tangan-palsu',
        ])->assertStatus(403);

        expect($order->refresh()->payment_status)->toBe(PaymentStatus::Unpaid);
    });

    it('rejects a notification with no signature at all', function () {
        $order = Order::factory()->online()->create();

        $this->postJson(route('payment.notification'), [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => '55000.00',
            'transaction_status' => 'settlement',
        ])->assertStatus(403);

        expect($order->refresh()->payment_status)->toBe(PaymentStatus::Unpaid);
    });

    it('rejects an incomplete notification', function () {
        $this->postJson(route('payment.notification'), ['order_id' => 'ORD-X'])
            ->assertStatus(403);
    });

    it('returns 404 for an order code it does not know', function () {
        $payload = signMidtransPayload([
            'order_id' => 'ORD-TIDAK-ADA',
            'status_code' => '200',
            'gross_amount' => '10000.00',
            'transaction_status' => 'settlement',
        ], 'SB-Mid-server-test');

        $this->postJson(route('payment.notification'), $payload)->assertNotFound();
    });

    it('ignores a repeat notification for an order already paid', function () {
        $order = Order::factory()->online()->paid()->create([
            'grand_total' => 10000,
            'midtrans_transaction_id' => 'trx-first',
        ]);

        $payload = signMidtransPayload([
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => '10000.00',
            'transaction_status' => 'expire',
        ], 'SB-Mid-server-test');

        $this->postJson(route('payment.notification'), $payload)->assertOk();

        $order->refresh();

        expect($order->payment_status)->toBe(PaymentStatus::Paid)
            ->and($order->midtrans_transaction_id)->toBe('trx-first');
    });

    it('accepts the webhook without a csrf token', function () {
        $order = Order::factory()->online()->create(['grand_total' => 10000]);

        $payload = signMidtransPayload([
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => '10000.00',
            'transaction_status' => 'settlement',
        ], 'SB-Mid-server-test');

        $this->post(route('payment.notification'), $payload)->assertOk();
    });
});

describe('gateway unit behaviour', function () {
    it('reads a signed notification into a normalised result', function () {
        fakeMidtransCredentials('server-key-xyz');

        $gateway = app(MidtransGateway::class);

        $payload = signMidtransPayload([
            'order_id' => 'ORD-123',
            'status_code' => '200',
            'gross_amount' => '12000.00',
            'transaction_status' => 'settlement',
            'transaction_id' => 'trx-9',
        ], 'server-key-xyz');

        $notification = $gateway->readNotification($payload);

        expect($notification)->toBeInstanceOf(PaymentNotification::class)
            ->and($notification->orderCode)->toBe('ORD-123')
            ->and($notification->paymentStatus)->toBe(PaymentStatus::Paid)
            ->and($notification->transactionId)->toBe('trx-9')
            ->and($notification->isPaid())->toBeTrue();
    });

    it('refuses to open a payment session without credentials', function () {
        config()->set('midtrans.server_key', null);
        config()->set('midtrans.client_key', null);

        $gateway = app(MidtransGateway::class);
        $order = Order::factory()->online()->create();

        expect(fn () => $gateway->createPaymentToken($order))
            ->toThrow(RuntimeException::class);
    });
});

describe('payment finish redirect', function () {
    it('congratulates a customer whose payment went through', function () {
        $order = Order::factory()->online()->paid()->create();
        session()->put('recent_order_codes', [$order->order_code]);

        $this->get(route('payment.finish', $order->order_code))
            ->assertRedirect(route('checkout.success', $order->order_code))
            ->assertSessionHas('success');
    });

    it('tells a customer their payment is still being verified', function () {
        $order = Order::factory()->online()->create();
        session()->put('recent_order_codes', [$order->order_code]);

        $this->get(route('payment.finish', $order->order_code))
            ->assertRedirect(route('checkout.success', $order->order_code))
            ->assertSessionHas('info');
    });

    it('reports a failed payment', function () {
        $order = Order::factory()->online()->create(['payment_status' => PaymentStatus::Failed]);
        session()->put('recent_order_codes', [$order->order_code]);

        $this->get(route('payment.finish', $order->order_code))
            ->assertRedirect(route('checkout.success', $order->order_code))
            ->assertSessionHas('error');
    });
});
