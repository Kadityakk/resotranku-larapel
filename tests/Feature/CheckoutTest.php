<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Item;
use App\Models\Order;
use Database\Seeders\CategorySeeder;

beforeEach(function () {
    $this->seed(CategorySeeder::class);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function checkoutPayload(array $overrides = []): array
{
    return [
        'customer_name' => 'Budi Santoso',
        'customer_phone' => '081234567890',
        'table_number' => 5,
        'payment_method' => PaymentMethod::Cash->value,
        'note' => 'Tidak pakai sambal',
        ...$overrides,
    ];
}

it('sends an empty cart back to the menu', function () {
    $this->get(route('checkout'))
        ->assertRedirect(route('menu'))
        ->assertSessionHas('error');
});

it('shows the checkout page with the cart contents', function () {
    $item = Item::factory()->create(['price' => 45000]);
    $this->postJson(route('cart.add'), ['id' => $item->id]);

    $this->get(route('checkout'))
        ->assertOk()
        ->assertSee($item->name)
        ->assertSee('Rp45.000')
        ->assertSee('Tunai')
        ->assertSee('QRIS');
});

it('prefills the table number detected from the qr code', function () {
    $item = Item::factory()->create();
    $this->get('/menu?meja=12');
    $this->postJson(route('cart.add'), ['id' => $item->id]);

    $this->get(route('checkout'))
        ->assertOk()
        ->assertSee('value="12"', false);
});

it('places a cash order and lands on the success page', function () {
    $item = Item::factory()->create(['price' => 30000]);
    $this->postJson(route('cart.add'), ['id' => $item->id, 'qty' => 2]);

    $response = $this->post(route('checkout.store'), checkoutPayload());

    $order = Order::sole();

    $response->assertRedirect(route('checkout.success', $order->order_code));

    expect($order->customer_name)->toBe('Budi Santoso')
        ->and($order->table_number)->toBe(5)
        ->and($order->payment_method)->toBe(PaymentMethod::Cash)
        ->and($order->payment_status)->toBe(PaymentStatus::Unpaid)
        ->and($order->status)->toBe(OrderStatus::Pending)
        ->and($order->subtotal)->toBe(60000)
        ->and($order->tax)->toBe(6000)
        ->and($order->grand_total)->toBe(66000)
        ->and($order->note)->toBe('Tidak pakai sambal');
});

it('stores one order item per cart line', function () {
    $items = Item::factory()->count(2)->create(['price' => 20000]);

    foreach ($items as $item) {
        $this->postJson(route('cart.add'), ['id' => $item->id, 'qty' => 2]);
    }

    $this->post(route('checkout.store'), checkoutPayload());

    $order = Order::with('orderItems')->sole();

    expect($order->orderItems)->toHaveCount(2);

    $orderItem = $order->orderItems->first();

    expect($orderItem->quantity)->toBe(2)
        ->and($orderItem->price)->toBe(20000)
        ->and($orderItem->tax)->toBe(4000)
        ->and($orderItem->total_price)->toBe(44000);
});

it('empties the cart once the order is stored', function () {
    $item = Item::factory()->create();
    $this->postJson(route('cart.add'), ['id' => $item->id]);

    $this->post(route('checkout.store'), checkoutPayload());

    expect(session('cart'))->toBeNull();
});

it('charges the current database price, not the price held in the session', function () {
    $item = Item::factory()->create(['price' => 10000]);
    $this->postJson(route('cart.add'), ['id' => $item->id]);

    $item->update(['price' => 99000]);

    $this->post(route('checkout.store'), checkoutPayload());

    expect(Order::sole()->subtotal)->toBe(99000);
});

it('refuses to place an order containing a menu that went inactive', function () {
    $item = Item::factory()->create();
    $this->postJson(route('cart.add'), ['id' => $item->id]);

    $item->update(['is_active' => false]);

    $this->post(route('checkout.store'), checkoutPayload())
        ->assertSessionHasErrors('cart');

    expect(Order::count())->toBe(0);
});

it('refuses to place an order with an empty cart', function () {
    $this->post(route('checkout.store'), checkoutPayload())
        ->assertSessionHasErrors('cart');

    expect(Order::count())->toBe(0);
});

it('validates the checkout form', function (array $payload, string $field) {
    $item = Item::factory()->create();
    $this->postJson(route('cart.add'), ['id' => $item->id]);

    $this->post(route('checkout.store'), checkoutPayload($payload))
        ->assertSessionHasErrors($field);

    expect(Order::count())->toBe(0);
})->with([
    'missing name' => [['customer_name' => ''], 'customer_name'],
    'missing phone' => [['customer_phone' => ''], 'customer_phone'],
    'phone with letters' => [['customer_phone' => 'bukan nomor'], 'customer_phone'],
    'missing table' => [['table_number' => ''], 'table_number'],
    'table out of range' => [['table_number' => 9999], 'table_number'],
    'unknown payment method' => [['payment_method' => 'bitcoin'], 'payment_method'],
]);

it('gives every order a unique code', function () {
    $item = Item::factory()->create();

    foreach (range(1, 3) as $round) {
        $this->postJson(route('cart.add'), ['id' => $item->id]);
        $this->post(route('checkout.store'), checkoutPayload());
    }

    expect(Order::pluck('order_code')->unique())->toHaveCount(3);
});

it('shows the order success page to the browser that placed the order', function () {
    $item = Item::factory()->create();
    $this->postJson(route('cart.add'), ['id' => $item->id]);
    $this->post(route('checkout.store'), checkoutPayload());

    $order = Order::sole();

    $this->get(route('checkout.success', $order->order_code))
        ->assertOk()
        ->assertSee($order->order_code)
        ->assertSee('Silakan bayar di kasir');
});

it('hides the order success page from someone who did not place the order', function () {
    $order = Order::factory()->create();

    $this->get(route('checkout.success', $order->order_code))->assertNotFound();
});

it('sends an online order to the payment page instead of the success page', function () {
    $item = Item::factory()->create();
    $this->postJson(route('cart.add'), ['id' => $item->id]);

    $this->post(route('checkout.store'), checkoutPayload([
        'payment_method' => PaymentMethod::Qris->value,
    ]));

    $order = Order::sole();

    expect($order->payment_method)->toBe(PaymentMethod::Qris);

    $this->followingRedirects();
    $this->get(route('payment.show', $order->order_code))->assertOk();
})->skip('covered by PaymentTest, which fakes the gateway');
