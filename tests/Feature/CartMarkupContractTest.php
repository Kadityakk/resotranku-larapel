<?php

use App\Models\Item;
use Database\Seeders\CategorySeeder;
use Illuminate\Support\Facades\File;

/**
 * public/assets/customer/js/cart.js finds everything it needs through data
 * attributes. If Blade stops rendering one of them the order bar silently stops
 * working in the browser while every server-side test still passes, so the
 * contract between the two is asserted here.
 */
beforeEach(function () {
    $this->seed(CategorySeeder::class);
    $this->cartJs = file_get_contents(public_path('assets/customer/js/cart.js'));
});

it('loads the cart script and stylesheet on customer pages', function () {
    $this->get(route('menu'))
        ->assertOk()
        ->assertSee('assets/customer/js/cart.js', false)
        ->assertSee('assets/customer/css/custom.css', false)
        ->assertSee('name="csrf-token"', false)
        ->assertSee('id="toast-stack"', false)
        ->assertSee('data-order-bar', false)
        ->assertSee('data-order-bar-body', false);
});

it('keeps css out of the blade templates', function () {
    $templates = File::allFiles(resource_path('views'));

    $offenders = collect($templates)
        ->filter(fn ($file) => str_contains($file->getContents(), '<style'))
        ->map(fn ($file) => $file->getRelativePathname())
        ->values();

    expect($offenders)->toBeEmpty();
});

it('reports the running quantity in every order response', function () {
    $item = Item::factory()->create();

    $this->postJson(route('cart.add'), ['id' => $item->id, 'qty' => 2])
        ->assertOk()
        ->assertJsonPath('cart_quantity', 2);

    $this->postJson(route('cart.update'), ['id' => $item->id, 'qty' => 7])
        ->assertOk()
        ->assertJsonPath('cart_quantity', 7);

    $this->postJson(route('cart.remove'), ['id' => $item->id])
        ->assertOk()
        ->assertJsonPath('cart_quantity', 0);
});

it('no longer links to a cart page from the navbar', function () {
    $this->get(route('menu'))
        ->assertOk()
        ->assertDontSee('id="cart-badge"', false)
        ->assertDontSee('fa-shopping-bag', false);
});

it('ships the order bar animation the script triggers', function () {
    $css = file_get_contents(public_path('assets/customer/css/custom.css'));

    expect($css)->toContain('.rk-orderbar.is-bumped')
        ->and($css)->toContain('@keyframes rk-badge-pop')
        ->and($css)->toContain('.rk-orderbar.is-open .rk-orderbar__panel')
        ->and($this->cartJs)->toContain("classList.add('is-bumped')");
});

it('renders an add-to-cart button carrying the attributes the script reads', function () {
    $item = Item::factory()->create();

    $html = $this->get(route('menu'))->assertOk()->getContent();

    expect($html)
        ->toContain('data-cart-add')
        ->toContain('data-item-id="'.$item->id.'"')
        ->toContain('data-url="'.route('cart.add').'"');
});

it('never gives a menu button an empty quantity source', function () {
    Item::factory()->create();

    $html = $this->get(route('menu'))->assertOk()->getContent();

    expect($html)->not->toContain('data-qty-source=""');
});

it('only queries a quantity source when one is actually set', function () {
    expect($this->cartJs)->not->toContain("getAttribute('data-qty-source') || ''");
});

it('renders order bar rows carrying the attributes the script reads', function () {
    $item = Item::factory()->create(['price' => 25000]);
    $this->postJson(route('cart.add'), ['id' => $item->id, 'qty' => 2]);

    $html = $this->get(route('menu'))->assertOk()->getContent();

    expect($html)
        ->toContain('data-cart-line="'.$item->id.'"')
        ->toContain('data-qty-input')
        ->toContain('data-cart-step="-1"')
        ->toContain('data-cart-step="1"')
        ->toContain('data-cart-remove')
        ->toContain('data-cart-note')
        ->toContain('data-order-bar-toggle')
        ->toContain('data-url="'.route('cart.update').'"')
        ->toContain('data-url="'.route('cart.remove').'"')
        ->toContain('href="'.route('checkout').'"');
});

it('keeps every selector the script looks up present in the markup it runs against', function () {
    $item = Item::factory()->create();
    $this->postJson(route('cart.add'), ['id' => $item->id]);

    $menuPage = $this->get(route('menu'))->assertOk()->getContent();

    preg_match_all('/\[data-[a-z-]+/', $this->cartJs, $matches);

    $selectors = collect($matches[0])
        ->map(fn (string $selector) => ltrim($selector, '['))
        ->unique()
        ->values();

    expect($selectors)->not->toBeEmpty();

    foreach ($selectors as $selector) {
        expect($menuPage)->toContain($selector);
    }
});
