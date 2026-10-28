<?php

use App\Enums\OrderStatus;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

beforeEach(function () {
    $this->chef = User::factory()->chef()->create();
});

it('groups the kitchen board into confirmed, cooking and cooked columns', function () {
    $confirmed = Order::factory()->status(OrderStatus::Confirmed)->create();
    $cooking = Order::factory()->status(OrderStatus::Cooking)->create();
    $cooked = Order::factory()->status(OrderStatus::Cooked)->create();

    $this->actingAs($this->chef)
        ->get(route('chef.orders.index'))
        ->assertOk()
        ->assertSee($confirmed->order_code)
        ->assertSee($cooking->order_code)
        ->assertSee($cooked->order_code)
        ->assertSee('Dikonfirmasi')
        ->assertSee('Sedang Dimasak')
        ->assertSee('Siap Disajikan');
});

it('keeps pending, served and cancelled orders off the board', function (OrderStatus $status) {
    $order = Order::factory()->status($status)->create();

    $this->actingAs($this->chef)
        ->get(route('chef.orders.index'))
        ->assertOk()
        ->assertDontSee($order->order_code);
})->with([
    'pending' => [OrderStatus::Pending],
    'served' => [OrderStatus::Served],
    'cancelled' => [OrderStatus::Cancelled],
]);

it('shows the kitchen queue oldest first', function () {
    $newer = Order::factory()->status(OrderStatus::Confirmed)->create(['created_at' => now()]);
    $older = Order::factory()->status(OrderStatus::Confirmed)->create(['created_at' => now()->subHour()]);

    $this->actingAs($this->chef)
        ->get(route('chef.orders.index'))
        ->assertOk()
        ->assertSeeInOrder([$older->order_code, $newer->order_code]);
});

it('starts cooking a confirmed order', function () {
    $order = Order::factory()->status(OrderStatus::Confirmed)->create();

    $this->actingAs($this->chef)
        ->from(route('chef.orders.index'))
        ->patch(route('chef.orders.status', $order), ['status' => OrderStatus::Cooking->value])
        ->assertRedirect(route('chef.orders.index'))
        ->assertSessionHas('success');

    expect($order->refresh()->status)->toBe(OrderStatus::Cooking);
});

it('walks an order from confirmed through to served', function () {
    $order = Order::factory()->status(OrderStatus::Confirmed)->create();

    foreach ([OrderStatus::Cooking, OrderStatus::Cooked, OrderStatus::Served] as $stage) {
        $this->actingAs($this->chef)
            ->from(route('chef.orders.index'))
            ->patch(route('chef.orders.status', $order), ['status' => $stage->value])
            ->assertSessionHasNoErrors();

        expect($order->refresh()->status)->toBe($stage);
    }
});

it('refuses a kitchen status jump that skips a stage', function () {
    $order = Order::factory()->status(OrderStatus::Confirmed)->create();

    $this->actingAs($this->chef)
        ->from(route('chef.orders.index'))
        ->patch(route('chef.orders.status', $order), ['status' => OrderStatus::Served->value])
        ->assertSessionHasErrors('status');

    expect($order->refresh()->status)->toBe(OrderStatus::Confirmed);
});

it('will not let the kitchen confirm or cancel an order', function (OrderStatus $status) {
    $order = Order::factory()->status(OrderStatus::Confirmed)->create();

    $this->actingAs($this->chef)
        ->from(route('chef.orders.index'))
        ->patch(route('chef.orders.status', $order), ['status' => $status->value])
        ->assertForbidden();

    expect($order->refresh()->status)->toBe(OrderStatus::Confirmed);
})->with([
    'confirm' => [OrderStatus::Confirmed],
    'cancel' => [OrderStatus::Cancelled],
]);

it('shows what the kitchen needs to cook for one order', function () {
    $item = Item::factory()->create(['name' => 'Soto Ayam Lamongan']);
    $order = Order::factory()->status(OrderStatus::Confirmed)->create(['note' => 'Tanpa bawang goreng']);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'item_id' => $item->id,
        'quantity' => 2,
    ]);

    $this->actingAs($this->chef)
        ->get(route('chef.orders.show', $order))
        ->assertOk()
        ->assertSee('Soto Ayam Lamongan')
        ->assertSee('Tanpa bawang goreng')
        ->assertSee('2&times;', false);
});

it('counts how many orders were served today', function () {
    Order::factory()->count(2)->status(OrderStatus::Served)->create(['updated_at' => now()]);
    Order::factory()->status(OrderStatus::Served)->create(['updated_at' => now()->subWeek()]);

    $this->actingAs($this->chef)
        ->get(route('chef.orders.index'))
        ->assertOk()
        ->assertSeeInOrder(['2', 'Disajikan Hari Ini'], false);
});

it('rejects an unknown status from the kitchen', function () {
    $order = Order::factory()->status(OrderStatus::Confirmed)->create();

    $this->actingAs($this->chef)
        ->from(route('chef.orders.index'))
        ->patch(route('chef.orders.status', $order), ['status' => 'hangus'])
        ->assertSessionHasErrors('status');
});
