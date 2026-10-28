<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Item;
use Database\Seeders\CategorySeeder;

/**
 * The customer template ships Bootstrap 5.0, which is older than several
 * utilities used in the markup. A class that version does not define renders as
 * nothing - the cart badge became white text on no background - and no
 * server-side assertion can see it. So the stylesheet contract is checked here.
 */
beforeEach(function () {
    $this->customerBootstrap = file_get_contents(public_path('assets/customer/css/bootstrap.min.css'));
    $this->customCss = file_get_contents(public_path('assets/customer/css/custom.css'));
});

it('loads the override stylesheet after the template stylesheets', function () {
    $this->seed(CategorySeeder::class);

    $html = $this->get(route('menu'))->assertOk()->getContent();

    $bootstrapAt = strpos($html, 'assets/customer/css/bootstrap.min.css');
    $templateAt = strpos($html, 'assets/customer/css/style.css');
    $customAt = strpos($html, 'assets/customer/css/custom.css');

    expect($bootstrapAt)->not->toBeFalse()
        ->and($templateAt)->not->toBeFalse()
        ->and($customAt)->not->toBeFalse()
        ->and($customAt)->toBeGreaterThan($bootstrapAt)
        ->and($customAt)->toBeGreaterThan($templateAt);
});

it('defines every text-bg utility the customer pages ask for', function () {
    $referenced = collect([
        // Rendered literally in the customer layouts.
        'primary', 'secondary',
        // Rendered through <x-status-badge> on the order and payment pages.
        ...collect(OrderStatus::cases())->map(fn (OrderStatus $s) => $s->color()),
        ...collect(PaymentStatus::cases())->map(fn (PaymentStatus $s) => $s->color()),
    ])->unique()->values();

    expect($referenced)->not->toBeEmpty();

    foreach ($referenced as $variant) {
        $class = '.text-bg-'.$variant;

        $definedByBootstrap = str_contains($this->customerBootstrap, $class);
        $definedByOverride = str_contains($this->customCss, $class);

        expect($definedByBootstrap || $definedByOverride)
            ->toBeTrue("text-bg-{$variant} is used on a customer page but no loaded stylesheet defines it");
    }
});

it('gives the cart badge a background so it is not white on white', function () {
    expect($this->customCss)->toMatch('/\.text-bg-primary\s*\{[^}]*background-color/s');
});

it('still needs the backfill, proving the guard is meaningful', function () {
    // If the template is ever upgraded past Bootstrap 5.2 this will fail, which
    // is the signal to drop the backfill rather than keep duplicating Bootstrap.
    expect($this->customerBootstrap)->not->toContain('.text-bg-primary');
});

it('positions the cart badge over the bag icon', function () {
    $this->seed(CategorySeeder::class);
    Item::factory()->create();

    $html = $this->get(route('menu'))->assertOk()->getContent();

    // The badge is absolutely positioned, so its anchor must establish a
    // containing block.
    expect($html)->toMatch('/position-relative[^>]*>\s*<i class="fa fa-shopping-bag/s')
        ->and($this->customCss)->toMatch('/\.cart-badge\s*\{[^}]*position:\s*absolute/s');
});
