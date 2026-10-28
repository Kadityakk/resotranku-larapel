<?php

use App\Models\Category;
use App\Models\Item;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->category = Category::factory()->create(['cat_name' => 'Makanan']);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function itemPayload(Category $category, array $overrides = []): array
{
    return [
        'name' => 'Nasi Goreng Spesial',
        'description' => 'Nasi goreng dengan telur, ayam dan acar segar.',
        'price' => 28000,
        'category_id' => $category->id,
        'is_active' => '1',
        ...$overrides,
    ];
}

it('lists every menu in a data table', function () {
    $items = Item::factory()->count(3)->create(['category_id' => $this->category->id]);

    $response = $this->actingAs($this->admin)->get(route('admin.items.index'));

    $response->assertOk()->assertSee('data-datatable', false);

    foreach ($items as $item) {
        $response->assertSee($item->name);
    }
});

it('stores a new menu', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.items.store'), itemPayload($this->category))
        ->assertRedirect(route('admin.items.index'))
        ->assertSessionHas('success');

    $item = Item::sole();

    expect($item->name)->toBe('Nasi Goreng Spesial')
        ->and($item->price)->toBe(28000)
        ->and($item->is_active)->toBeTrue()
        ->and($item->category_id)->toBe($this->category->id);
});

it('stores an uploaded image under a generated name', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)->post(route('admin.items.store'), itemPayload($this->category, [
        'img' => UploadedFile::fake()->image('../../evil.jpg'),
    ]));

    $item = Item::sole();

    expect($item->img)->toStartWith('items/')
        ->and($item->img)->not->toContain('evil');

    Storage::disk('public')->assertExists($item->img);
});

it('rejects a file that is not an image', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)->post(route('admin.items.store'), itemPayload($this->category, [
        'img' => UploadedFile::fake()->create('menu.pdf', 100, 'application/pdf'),
    ]))->assertSessionHasErrors('img');

    expect(Item::count())->toBe(0);
});

it('validates the menu form', function (array $payload, string $field) {
    $this->actingAs($this->admin)
        ->post(route('admin.items.store'), itemPayload($this->category, $payload))
        ->assertSessionHasErrors($field);

    expect(Item::count())->toBe(0);
})->with([
    'missing name' => [['name' => ''], 'name'],
    'missing description' => [['description' => ''], 'description'],
    'missing price' => [['price' => ''], 'price'],
    'negative price' => [['price' => -500], 'price'],
    'non numeric price' => [['price' => 'gratis'], 'price'],
    'unknown category' => [['category_id' => 9999], 'category_id'],
]);

it('treats an unchecked availability switch as inactive', function () {
    $this->actingAs($this->admin)->post(route('admin.items.store'), itemPayload($this->category, [
        'is_active' => '0',
    ]));

    expect(Item::sole()->is_active)->toBeFalse();
});

it('updates a menu', function () {
    $item = Item::factory()->create(['category_id' => $this->category->id, 'price' => 10000]);

    $this->actingAs($this->admin)
        ->put(route('admin.items.update', $item), itemPayload($this->category, [
            'name' => 'Nasi Goreng Diperbarui',
            'price' => 35000,
        ]))
        ->assertRedirect(route('admin.items.index'))
        ->assertSessionHas('success');

    $item->refresh();

    expect($item->name)->toBe('Nasi Goreng Diperbarui')
        ->and($item->price)->toBe(35000);
});

it('keeps the current image when no new file is uploaded', function () {
    $item = Item::factory()->create([
        'category_id' => $this->category->id,
        'img' => 'items/original.jpg',
    ]);

    $this->actingAs($this->admin)->put(route('admin.items.update', $item), itemPayload($this->category));

    expect($item->refresh()->img)->toBe('items/original.jpg');
});

it('replaces the old image file when a new one is uploaded', function () {
    Storage::fake('public');
    Storage::disk('public')->put('items/original.jpg', 'lama');

    $item = Item::factory()->create([
        'category_id' => $this->category->id,
        'img' => 'items/original.jpg',
    ]);

    $this->actingAs($this->admin)->put(route('admin.items.update', $item), itemPayload($this->category, [
        'img' => UploadedFile::fake()->image('baru.jpg'),
    ]));

    expect($item->refresh()->img)->not->toBe('items/original.jpg');

    Storage::disk('public')->assertMissing('items/original.jpg');
    Storage::disk('public')->assertExists($item->img);
});

it('deletes a menu that was never ordered', function () {
    Storage::fake('public');
    Storage::disk('public')->put('items/gone.jpg', 'isi');

    $item = Item::factory()->create(['category_id' => $this->category->id, 'img' => 'items/gone.jpg']);

    $this->actingAs($this->admin)
        ->delete(route('admin.items.destroy', $item))
        ->assertRedirect(route('admin.items.index'))
        ->assertSessionHas('success');

    expect(Item::withTrashed()->count())->toBe(0);
    Storage::disk('public')->assertMissing('items/gone.jpg');
});

it('archives rather than erases a menu that already appears on an order', function () {
    $item = Item::factory()->create(['category_id' => $this->category->id]);
    OrderItem::factory()->create(['item_id' => $item->id]);

    $this->actingAs($this->admin)
        ->delete(route('admin.items.destroy', $item))
        ->assertRedirect(route('admin.items.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($item);

    expect(Item::withTrashed()->find($item->id)->is_active)->toBeFalse();
});

it('toggles availability from the menu table', function () {
    $item = Item::factory()->create(['category_id' => $this->category->id, 'is_active' => true]);

    $this->actingAs($this->admin)
        ->from(route('admin.items.index'))
        ->patch(route('admin.items.toggle', $item))
        ->assertRedirect(route('admin.items.index'))
        ->assertSessionHas('success');

    expect($item->refresh()->is_active)->toBeFalse();

    $this->actingAs($this->admin)->patch(route('admin.items.toggle', $item));

    expect($item->refresh()->is_active)->toBeTrue();
});

it('shows a single menu', function () {
    $item = Item::factory()->create(['category_id' => $this->category->id]);

    $this->actingAs($this->admin)
        ->get(route('admin.items.show', $item))
        ->assertOk()
        ->assertSee($item->name)
        ->assertSee('Makanan');
});

it('refuses menu management to a non-admin', function () {
    $cashier = User::factory()->cashier()->create();

    $this->actingAs($cashier)
        ->post(route('admin.items.store'), itemPayload($this->category))
        ->assertForbidden();

    expect(Item::count())->toBe(0);
});

it('hides inactive menus from the customer menu page', function () {
    $visible = Item::factory()->create(['category_id' => $this->category->id]);
    $hidden = Item::factory()->inactive()->create(['category_id' => $this->category->id]);

    $this->get(route('menu'))
        ->assertOk()
        ->assertSee($visible->name)
        ->assertDontSee($hidden->name);
});
