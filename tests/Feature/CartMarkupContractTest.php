<?php

use App\Models\Item;
use Database\Seeders\CategorySeeder;
use Illuminate\Support\Facades\File;

/**
 * public/assets/customer/js/cart.js finds everything it needs through data
 * attributes. If Blade stops rendering one of them the cart silently stops
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
        ->assertSee('id="cart-badge"', false);
});

it('keeps css out of the blade templates', function () {
    $templates = File::allFiles(resource_path('views'));

    $offenders = collect($templates)
        ->filter(fn ($file) => str_contains($file->getContents(), '<style'))
        ->map(fn ($file) => $file->getRelativePathname())
        ->values();

    expect($offenders)->toBeEmpty();
});

it('hides the cart badge while the cart is empty', function () {
    $html = $this->get(route('menu'))->assertOk()->getContent();

    expect($html)->toMatch('/id="cart-badge"[^>]*\bd-none\b/s');
});

it('shows the cart badge with the total quantity once items are added', function () {
    $first = Item::factory()->create();
    $second = Item::factory()->create();

    $this->postJson(route('cart.add'), ['id' => $first->id, 'qty' => 2]);
    $this->postJson(route('cart.add'), ['id' => $second->id, 'qty' => 3]);

    $html = $this->get(route('menu'))->assertOk()->getContent();

    expect($html)
        ->not->toMatch('/id="cart-badge"[^>]*\bd-none\b/s')
        ->toMatch('/id="cart-badge".*?>\s*5\s*</s');
});

it('reports the running cart quantity in every cart response', function () {
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

it('shows the badge on every page that carries the navbar', function (string $routeName) {
    $item = Item::factory()->create();
    $this->postJson(route('cart.add'), ['id' => $item->id, 'qty' => 4]);

    $html = $this->get(route($routeName))->assertOk()->getContent();

    expect($html)->toMatch('/id="cart-badge".*?>\s*4\s*</s');
})->with(['menu', 'cart', 'checkout']);

it('ships the badge animation the script triggers', function () {
    $css = file_get_contents(public_path('assets/customer/css/custom.css'));

    expect($css)->toContain('.cart-badge.is-bumped')
        ->and($css)->toContain('@keyframes rk-badge-pop')
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

it('renders cart rows carrying the attributes the script reads', function () {
    $item = Item::factory()->create(['price' => 25000]);
    $this->postJson(route('cart.add'), ['id' => $item->id, 'qty' => 2]);

    $html = $this->get(route('cart'))->assertOk()->getContent();

    expect($html)
        ->toContain('data-cart-line="'.$item->id.'"')
        ->toContain('data-price="'.$item->price.'"')
        ->toContain('data-qty-input')
        ->toContain('data-line-total')
        ->toContain('data-cart-step="-1"')
        ->toContain('data-cart-step="1"')
        ->toContain('data-cart-remove')
        ->toContain('data-url="'.route('cart.update').'"')
        ->toContain('data-url="'.route('cart.remove').'"');
});

it('renders the summary targets the script rewrites after each change', function () {
    $item = Item::factory()->create();
    $this->postJson(route('cart.add'), ['id' => $item->id]);

    $html = $this->get(route('cart'))->assertOk()->getContent();

    expect($html)
        ->toContain('data-summary-subtotal')
        ->toContain('data-summary-tax')
        ->toContain('data-summary-total');
});

it('keeps every selector the script looks up present in the markup it runs against', function () {
    $item = Item::factory()->create();
    $this->postJson(route('cart.add'), ['id' => $item->id]);

    $cartPage = $this->get(route('cart'))->assertOk()->getContent();
    $menuPage = $this->get(route('menu'))->assertOk()->getContent();
    $combined = $cartPage.$menuPage;

    preg_match_all('/\[data-[a-z-]+/', $this->cartJs, $matches);

    $selectors = collect($matches[0])
        ->map(fn (string $selector) => ltrim($selector, '['))
        ->unique()
        ->values();

    expect($selectors)->not->toBeEmpty();

    foreach ($selectors as $selector) {
        expect($combined)->toContain($selector);
    }
});
