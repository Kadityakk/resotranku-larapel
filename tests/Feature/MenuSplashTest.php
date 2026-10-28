<?php

use App\Models\Item;
use Database\Seeders\CategorySeeder;

beforeEach(function () {
    $this->seed(CategorySeeder::class);
});

it('shows the three newest menus in the splash band', function () {
    $oldest = Item::factory()->create(['name' => 'Menu Paling Lama', 'created_at' => now()->subDays(10)]);
    $third = Item::factory()->create(['name' => 'Menu Ketiga', 'created_at' => now()->subDays(3)]);
    $second = Item::factory()->create(['name' => 'Menu Kedua', 'created_at' => now()->subDays(2)]);
    $newest = Item::factory()->create(['name' => 'Menu Terbaru', 'created_at' => now()->subDay()]);

    $response = $this->get(route('menu'))->assertOk();

    $response->assertSee('Menu Terbaru', false);

    $splash = splashMarkup($response->getContent());

    expect($splash)
        ->toContain($newest->name)
        ->toContain($second->name)
        ->toContain($third->name)
        ->not->toContain($oldest->name);
});

it('renders exactly three menus per pass of the moving band', function () {
    Item::factory()->count(8)->create();

    $splash = splashMarkup($this->get(route('menu'))->assertOk()->getContent());

    // The set is laid down three times so the sliding loop has no visible seam.
    expect(substr_count($splash, 'rk-splash__set'))->toBe(3)
        ->and(substr_count($splash, 'rk-splash__item'))->toBe(9)
        ->and(preg_match_all('/<img\b/', $splash))->toBe(9);
});

it('exposes only the first pass to screen readers and keyboard users', function () {
    Item::factory()->count(3)->create();

    $splash = splashMarkup($this->get(route('menu'))->assertOk()->getContent());

    expect(substr_count($splash, 'rk-splash__set" aria-hidden="false"'))->toBe(1)
        ->and(substr_count($splash, 'rk-splash__set" aria-hidden="true"'))->toBe(2)
        ->and(substr_count($splash, 'tabindex="0"'))->toBe(3)
        ->and(substr_count($splash, 'tabindex="-1"'))->toBe(6);
});

it('orders the splash newest first', function () {
    $a = Item::factory()->create(['name' => 'Aaa Lama', 'created_at' => now()->subDays(3)]);
    $b = Item::factory()->create(['name' => 'Bbb Sedang', 'created_at' => now()->subDays(2)]);
    $c = Item::factory()->create(['name' => 'Ccc Baru', 'created_at' => now()->subDay()]);

    $splash = splashMarkup($this->get(route('menu'))->assertOk()->getContent());

    $positions = [
        strpos($splash, $c->name),
        strpos($splash, $b->name),
        strpos($splash, $a->name),
    ];

    expect($positions[0])->toBeLessThan($positions[1])
        ->and($positions[1])->toBeLessThan($positions[2]);
});

it('leaves out menus that are not active', function () {
    $hidden = Item::factory()->inactive()->create(['name' => 'Menu Disembunyikan', 'created_at' => now()]);
    Item::factory()->count(3)->create(['created_at' => now()->subDay()]);

    $splash = splashMarkup($this->get(route('menu'))->assertOk()->getContent());

    expect($splash)->not->toContain($hidden->name);
});

it('shows each splash image with its name and price', function () {
    $item = Item::factory()->create(['name' => 'Rendang Daging', 'price' => 47000]);

    $splash = splashMarkup($this->get(route('menu'))->assertOk()->getContent());

    expect($splash)
        ->toContain('Rendang Daging')
        ->toContain('Rp47.000')
        ->toContain(e($item->imageUrl())); // Blade escapes the query string
});

it('points every splash image at a menu card that exists on the page', function () {
    Item::factory()->count(6)->create();

    $html = $this->get(route('menu'))->assertOk()->getContent();

    preg_match_all('/href="#(menu-item-\d+)"/', splashMarkup($html), $anchors);
    preg_match_all('/id="(menu-item-\d+)"/', $html, $targets);

    // Three distinct menus, repeated across the three passes of the track.
    expect($anchors[1])->toHaveCount(9)
        ->and(array_unique($anchors[1]))->toHaveCount(3);

    $dangling = array_diff($anchors[1], $targets[1]);

    expect($dangling)->toBeEmpty();
});

it('renders fewer tiles when fewer menus exist', function (int $count) {
    Item::factory()->count($count)->create();

    $splash = splashMarkup($this->get(route('menu'))->assertOk()->getContent());

    expect(substr_count($splash, 'rk-splash__item'))->toBe($count * 3);
})->with([1, 2]);

it('hides the splash band when there are no menus at all', function () {
    $this->get(route('menu'))
        ->assertOk()
        ->assertDontSee('rk-splash__track', false);
});

it('hides the splash band while a category filter is active', function () {
    $item = Item::factory()->create();

    $this->get(route('menu', ['category' => $item->category_id]))
        ->assertOk()
        ->assertDontSee('rk-splash__track', false);
});

it('hides the splash band while a search is active', function () {
    Item::factory()->create(['name' => 'Nasi Goreng']);

    $this->get(route('menu', ['q' => 'Nasi']))
        ->assertOk()
        ->assertDontSee('rk-splash__track', false);
});

it('ships the styles the splash markup relies on', function () {
    $css = file_get_contents(public_path('assets/customer/css/custom.css'));

    expect($css)
        ->toContain('.rk-splash__track')
        ->toContain('@keyframes rk-splash-slide')
        ->toContain('animation-play-state: paused')
        ->toContain('.rk-splash__item')
        ->toContain('.rk-splash__caption')
        ->toContain('.rk-splash__flag')
        ->toContain('scroll-margin-top');
});

/**
 * Narrows a menu page response to just the splash section, so assertions cannot
 * accidentally pass on the full menu grid rendered below it.
 */
function splashMarkup(string $html): string
{
    $start = strpos($html, 'rk-splash__track');

    if ($start === false) {
        return '';
    }

    $end = strpos($html, 'rk-chip', $start);

    return substr($html, $start, $end === false ? null : $end - $start);
}
