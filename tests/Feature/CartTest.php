<?php

use App\Models\Item;
use App\Services\CartService;
use Database\Seeders\CategorySeeder;

beforeEach(function () {
    $this->seed(CategorySeeder::class);
});

it('remembers the table number from the qr code query string', function () {
    $this->get('/menu?meja=7')->assertOk();

    expect(session(CartService::TABLE_SESSION_KEY))->toBe(7);
});

it('adds an item to the cart', function () {
    $item = Item::factory()->create(['price' => 25000]);

    $response = $this->postJson(route('cart.add'), ['id' => $item->id]);

    $response->assertOk()->assertJson([
        'success' => true,
        'cart_quantity' => 1,
        'totals' => ['subtotal' => 25000, 'tax' => 2500, 'grand_total' => 27500],
    ]);

    expect(session('cart')[$item->id]['qty'])->toBe(1);
});

it('increments the quantity when the same item is added twice', function () {
    $item = Item::factory()->create(['price' => 10000]);

    $this->postJson(route('cart.add'), ['id' => $item->id]);
    $response = $this->postJson(route('cart.add'), ['id' => $item->id, 'qty' => 2]);

    $response->assertOk()->assertJson(['cart_quantity' => 3]);
    expect(session('cart')[$item->id]['qty'])->toBe(3);
});

it('refuses to add an item that is not active', function () {
    $item = Item::factory()->inactive()->create();

    $this->postJson(route('cart.add'), ['id' => $item->id])
        ->assertStatus(422)
        ->assertJson(['success' => false]);

    expect(session('cart'))->toBeNull();
});

it('rejects adding a menu that does not exist', function () {
    $this->postJson(route('cart.add'), ['id' => 99999])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['id']]);
});

it('updates the quantity of a cart line', function () {
    $item = Item::factory()->create(['price' => 20000]);
    $this->postJson(route('cart.add'), ['id' => $item->id]);

    $response = $this->postJson(route('cart.update'), ['id' => $item->id, 'qty' => 4]);

    $response->assertOk()->assertJson([
        'cart_quantity' => 4,
        'totals' => ['subtotal' => 80000, 'tax' => 8000, 'grand_total' => 88000],
    ]);
});

it('removes the line when the quantity is set to zero', function () {
    $item = Item::factory()->create();
    $this->postJson(route('cart.add'), ['id' => $item->id]);

    $this->postJson(route('cart.update'), ['id' => $item->id, 'qty' => 0])
        ->assertOk()
        ->assertJson(['cart_quantity' => 0]);

    expect(session('cart'))->toBe([]);
});

it('does not update a line that is not in the cart', function () {
    $this->postJson(route('cart.update'), ['id' => 123, 'qty' => 2])
        ->assertStatus(422)
        ->assertJson(['success' => false]);
});

it('removes an item from the cart', function () {
    $item = Item::factory()->create();
    $this->postJson(route('cart.add'), ['id' => $item->id]);

    $this->postJson(route('cart.remove'), ['id' => $item->id])
        ->assertOk()
        ->assertJson(['success' => true, 'cart_quantity' => 0]);

    expect(session('cart'))->toBe([]);
});

it('reports an error when removing a line that is not in the cart', function () {
    $this->postJson(route('cart.remove'), ['id' => 404])
        ->assertStatus(422)
        ->assertJson(['success' => false]);
});

it('empties the whole cart', function () {
    $items = Item::factory()->count(3)->create();

    foreach ($items as $item) {
        $this->postJson(route('cart.add'), ['id' => $item->id]);
    }

    $this->delete(route('cart.clear'))
        ->assertRedirect(route('cart'))
        ->assertSessionHas('success');

    expect(session('cart'))->toBeNull();
});

it('shows the cart page with computed totals', function () {
    $item = Item::factory()->create(['price' => 30000]);
    $this->postJson(route('cart.add'), ['id' => $item->id, 'qty' => 2]);

    $this->get(route('cart'))
        ->assertOk()
        ->assertSee($item->name)
        ->assertSee('Rp60.000')
        ->assertSee('Rp6.000')
        ->assertSee('Rp66.000');
});

it('shows an empty state when the cart has no items', function () {
    $this->get(route('cart'))
        ->assertOk()
        ->assertSee('Keranjang Anda masih kosong');
});

it('rounds tax per line so the cart and the order never disagree', function () {
    $item = Item::factory()->create(['price' => 10005]);
    $this->postJson(route('cart.add'), ['id' => $item->id, 'qty' => 3]);

    $totals = app(CartService::class)->totals();

    expect($totals['subtotal'])->toBe(30015)
        ->and($totals['tax'])->toBe(3002)
        ->and($totals['grand_total'])->toBe(33017);
});
