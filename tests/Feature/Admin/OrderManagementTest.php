<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('lists orders in a data table', function () {
    $orders = Order::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.orders.index'));

    $response->assertOk()->assertSee('data-datatable', false);

    foreach ($orders as $order) {
        $response->assertSee($order->order_code)->assertSee($order->customer_name);
    }
});

it('filters orders by status', function () {
    $pending = Order::factory()->create();
    $served = Order::factory()->status(OrderStatus::Served)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.orders.index', ['status' => OrderStatus::Pending->value]))
        ->assertOk()
        ->assertSee($pending->order_code)
        ->assertDontSee($served->order_code);
});

it('filters orders by payment status', function () {
    $unpaid = Order::factory()->create();
    $paid = Order::factory()->paid()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.orders.index', ['payment_status' => PaymentStatus::Paid->value]))
        ->assertOk()
        ->assertSee($paid->order_code)
        ->assertDontSee($unpaid->order_code);
});

it('filters orders by date', function () {
    $today = Order::factory()->create();
    $old = Order::factory()->create(['created_at' => now()->subMonth()]);

    $this->actingAs($this->admin)
        ->get(route('admin.orders.index', ['date' => today()->toDateString()]))
        ->assertOk()
        ->assertSee($today->order_code)
        ->assertDontSee($old->order_code);
});

it('shows the full detail of an order', function () {
    $item = Item::factory()->create(['name' => 'Sate Ayam']);
    $order = Order::factory()->create(['note' => 'Pedas sekali']);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'item_id' => $item->id,
        'quantity' => 3,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee($order->order_code)
        ->assertSee($order->customer_name)
        ->assertSee($order->customer_phone)
        ->assertSee('Sate Ayam')
        ->assertSee('Pedas sekali');
});

it('advances an order one step along its lifecycle', function () {
    $order = Order::factory()->create();

    $this->actingAs($this->admin)
        ->from(route('admin.orders.show', $order))
        ->patch(route('admin.orders.status', $order), ['status' => OrderStatus::Confirmed->value])
        ->assertRedirect(route('admin.orders.show', $order))
        ->assertSessionHas('success');

    expect($order->refresh()->status)->toBe(OrderStatus::Confirmed);
});

it('walks an order through every stage in order', function () {
    $order = Order::factory()->create();

    $stages = [OrderStatus::Confirmed, OrderStatus::Cooking, OrderStatus::Cooked, OrderStatus::Served];

    foreach ($stages as $stage) {
        $this->actingAs($this->admin)
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.status', $order), ['status' => $stage->value])
            ->assertSessionHasNoErrors();

        expect($order->refresh()->status)->toBe($stage);
    }
});

it('refuses a status jump that skips a stage', function () {
    $order = Order::factory()->create();

    $this->actingAs($this->admin)
        ->from(route('admin.orders.show', $order))
        ->patch(route('admin.orders.status', $order), ['status' => OrderStatus::Served->value])
        ->assertSessionHasErrors('status');

    expect($order->refresh()->status)->toBe(OrderStatus::Pending);
});

it('refuses to move a finished order', function () {
    $order = Order::factory()->status(OrderStatus::Served)->create();

    $this->actingAs($this->admin)
        ->from(route('admin.orders.show', $order))
        ->patch(route('admin.orders.status', $order), ['status' => OrderStatus::Cooking->value])
        ->assertSessionHasErrors('status');

    expect($order->refresh()->status)->toBe(OrderStatus::Served);
});

it('allows cancelling from any stage that is not final', function (OrderStatus $from) {
    $order = Order::factory()->status($from)->create();

    $this->actingAs($this->admin)
        ->from(route('admin.orders.show', $order))
        ->patch(route('admin.orders.status', $order), ['status' => OrderStatus::Cancelled->value])
        ->assertSessionHasNoErrors();

    expect($order->refresh()->status)->toBe(OrderStatus::Cancelled);
})->with([
    'pending' => [OrderStatus::Pending],
    'confirmed' => [OrderStatus::Confirmed],
    'cooking' => [OrderStatus::Cooking],
    'cooked' => [OrderStatus::Cooked],
]);

it('rejects an unknown status value', function () {
    $order = Order::factory()->create();

    $this->actingAs($this->admin)
        ->from(route('admin.orders.show', $order))
        ->patch(route('admin.orders.status', $order), ['status' => 'terbakar'])
        ->assertSessionHasErrors('status');
});

it('counts only paid orders towards revenue on the dashboard', function () {
    Order::factory()->paid()->create(['grand_total' => 150000, 'paid_at' => now()]);
    Order::factory()->create(['grand_total' => 999000]);

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Rp150.000', 'Pendapatan Hari Ini'], false);
});

it('leaves revenue at zero when nothing has been paid for', function () {
    Order::factory()->count(2)->create(['grand_total' => 999000]);

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Rp0', 'Pendapatan Hari Ini'], false);
});

it('summarises how many orders sit at each status', function () {
    Order::factory()->count(2)->create();
    Order::factory()->status(OrderStatus::Cooking)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Menunggu Kasir'], false)
        ->assertSeeInOrder(['Sedang di Dapur'], false);
});
