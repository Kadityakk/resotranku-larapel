<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

beforeEach(function () {
    $this->cashier = User::factory()->cashier()->create();
});

it('shows pending orders in the till queue', function () {
    $pending = Order::factory()->create();
    $served = Order::factory()->status(OrderStatus::Served)->create();

    $this->actingAs($this->cashier)
        ->get(route('cashier.orders.index'))
        ->assertOk()
        ->assertSee($pending->order_code)
        ->assertSee('Menunggu Konfirmasi');

    expect($served->status)->toBe(OrderStatus::Served);
});

it('shows an empty queue message when nothing is waiting', function () {
    $this->actingAs($this->cashier)
        ->get(route('cashier.orders.index'))
        ->assertOk()
        ->assertSee('Tidak ada pesanan menunggu');
});

it('orders the queue oldest first', function () {
    $newer = Order::factory()->create(['created_at' => now()]);
    $older = Order::factory()->create(['created_at' => now()->subHour()]);

    $this->actingAs($this->cashier)
        ->get(route('cashier.orders.index'))
        ->assertOk()
        ->assertSeeInOrder([$older->order_code, $newer->order_code]);
});

it('confirms a cash order and records the payment', function () {
    $order = Order::factory()->create();

    $this->actingAs($this->cashier)
        ->patch(route('cashier.orders.confirm', $order))
        ->assertRedirect(route('cashier.orders.index'))
        ->assertSessionHas('success');

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Confirmed)
        ->and($order->payment_status)->toBe(PaymentStatus::Paid)
        ->and($order->paid_at)->not->toBeNull()
        ->and($order->user_id)->toBe($this->cashier->id);
});

it('confirms an online order that is already paid', function () {
    $order = Order::factory()->online()->paid()->create();

    $this->actingAs($this->cashier)
        ->patch(route('cashier.orders.confirm', $order))
        ->assertSessionHas('success');

    expect($order->refresh()->status)->toBe(OrderStatus::Confirmed);
});

it('refuses to confirm an online order whose payment has not arrived', function () {
    $order = Order::factory()->online()->create();

    $this->actingAs($this->cashier)
        ->from(route('cashier.orders.index'))
        ->patch(route('cashier.orders.confirm', $order))
        ->assertSessionHasErrors('order');

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Pending)
        ->and($order->payment_status)->toBe(PaymentStatus::Unpaid);
});

it('refuses to confirm the same order twice', function () {
    $order = Order::factory()->status(OrderStatus::Confirmed)->paid()->create();

    $this->actingAs($this->cashier)
        ->from(route('cashier.orders.index'))
        ->patch(route('cashier.orders.confirm', $order))
        ->assertSessionHasErrors('order');
});

it('does not overwrite a paid_at that is already set', function () {
    $paidAt = now()->subHours(2);
    $order = Order::factory()->online()->paid()->create(['paid_at' => $paidAt]);

    $this->actingAs($this->cashier)->patch(route('cashier.orders.confirm', $order));

    expect($order->refresh()->paid_at->timestamp)->toBe($paidAt->timestamp);
});

it('cancels a pending order', function () {
    $order = Order::factory()->create();

    $this->actingAs($this->cashier)
        ->patch(route('cashier.orders.cancel', $order))
        ->assertRedirect(route('cashier.orders.index'))
        ->assertSessionHas('success');

    expect($order->refresh()->status)->toBe(OrderStatus::Cancelled);
});

it('refuses to cancel an order that is already being processed', function () {
    $order = Order::factory()->status(OrderStatus::Cooking)->create();

    $this->actingAs($this->cashier)
        ->patch(route('cashier.orders.cancel', $order))
        ->assertStatus(422);

    expect($order->refresh()->status)->toBe(OrderStatus::Cooking);
});

it('shows the receipt for one order', function () {
    $item = Item::factory()->create(['name' => 'Rendang Daging']);
    $order = Order::factory()->create();
    OrderItem::factory()->create(['order_id' => $order->id, 'item_id' => $item->id]);

    $this->actingAs($this->cashier)
        ->get(route('cashier.orders.show', $order))
        ->assertOk()
        ->assertSee($order->order_code)
        ->assertSee($order->customer_name)
        ->assertSee('Rendang Daging')
        ->assertSee('Struk Pesanan');
});

it('hands a confirmed order over to the kitchen board', function () {
    $order = Order::factory()->create();
    $chef = User::factory()->chef()->create();

    $this->actingAs($this->cashier)->patch(route('cashier.orders.confirm', $order));

    $this->actingAs($chef)
        ->get(route('chef.orders.index'))
        ->assertOk()
        ->assertSee($order->order_code);
});

it('keeps an unconfirmed order off the kitchen board', function () {
    $order = Order::factory()->create();
    $chef = User::factory()->chef()->create();

    $this->actingAs($chef)
        ->get(route('chef.orders.index'))
        ->assertOk()
        ->assertDontSee($order->order_code);
});
