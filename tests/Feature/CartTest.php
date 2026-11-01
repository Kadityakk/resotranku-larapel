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

it('no longer has a separate cart page', function () {
    $this->get('/cart')->assertNotFound();
});

it('shows the picked menus in the order bar with computed totals', function () {
    $item = Item::factory()->create(['price' => 30000]);
    $this->postJson(route('cart.add'), ['id' => $item->id, 'qty' => 2]);

    $this->get(route('menu'))
        ->assertOk()
        ->assertSee('id="order-bar"', false)
        ->assertSee('data-cart-line="'.$item->id.'"', false)
        ->assertSee('Rp60.000')
        ->assertSee('Rp6.000')
        ->assertSee('Rp66.000');
});

it('hides the order bar while nothing is picked', function () {
    $html = $this->get(route('menu'))->assertOk()->getContent();

    expect($html)->toMatch('/id="order-bar"[^>]*\bis-empty\b/s')
        ->not->toContain('data-cart-line');
});

it('returns the re-rendered order bar after every change', function () {
    $item = Item::factory()->create(['price' => 15000]);

    $this->postJson(route('cart.add'), ['id' => $item->id, 'qty' => 2])
        ->assertOk()
        ->assertJsonPath('order_bar', fn (string $html) => str_contains($html, 'data-cart-line="'.$item->id.'"')
            && str_contains($html, 'Rp30.000'));
});

it('stores a message for a picked menu', function () {
    $item = Item::factory()->create();
    $this->postJson(route('cart.add'), ['id' => $item->id]);

    $this->postJson(route('cart.update'), ['id' => $item->id, 'note' => '  tanpa wasabi '])
        ->assertOk()
        ->assertJsonPath('order_bar', fn (string $html) => str_contains($html, 'value="tanpa wasabi"'));

    expect(session('cart')[$item->id]['note'])->toBe('tanpa wasabi')
        ->and(session('cart')[$item->id]['qty'])->toBe(1);
});

it('clears the message when it is emptied', function () {
    $item = Item::factory()->create();
    $this->postJson(route('cart.add'), ['id' => $item->id]);
    $this->postJson(route('cart.update'), ['id' => $item->id, 'note' => 'pedas']);

    $this->postJson(route('cart.update'), ['id' => $item->id, 'note' => ''])->assertOk();

    expect(session('cart')[$item->id]['note'])->toBeNull();
});

it('keeps the message when the same menu is added again', function () {
    $item = Item::factory()->create();
    $this->postJson(route('cart.add'), ['id' => $item->id]);
    $this->postJson(route('cart.update'), ['id' => $item->id, 'note' => 'pedas']);

    $this->postJson(route('cart.add'), ['id' => $item->id]);

    expect(session('cart')[$item->id])->toMatchArray(['qty' => 2, 'note' => 'pedas']);
});

it('rejects an update with neither a quantity nor a message', function () {
    $item = Item::factory()->create();
    $this->postJson(route('cart.add'), ['id' => $item->id]);

    $this->postJson(route('cart.update'), ['id' => $item->id])
        ->assertStatus(422)
        ->assertJsonValidationErrors('qty');
});

it('rejects a message that is too long', function () {
    $item = Item::factory()->create();
    $this->postJson(route('cart.add'), ['id' => $item->id]);

    $this->postJson(route('cart.update'), ['id' => $item->id, 'note' => str_repeat('a', 256)])
        ->assertStatus(422)
        ->assertJsonValidationErrors('note');
});

it('rounds tax per line so the cart and the order never disagree', function () {
    $item = Item::factory()->create(['price' => 10005]);
    $this->postJson(route('cart.add'), ['id' => $item->id, 'qty' => 3]);

    $totals = app(CartService::class)->totals();

    expect($totals['subtotal'])->toBe(30015)
        ->and($totals['tax'])->toBe(3002)
        ->and($totals['grand_total'])->toBe(33017);
});
