<?php

use App\Models\Category;
use App\Models\Item;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('lists categories with their menu counts', function () {
    $category = Category::factory()->create(['cat_name' => 'Makanan']);
    Item::factory()->count(2)->create(['category_id' => $category->id]);

    $this->actingAs($this->admin)
        ->get(route('admin.categories.index'))
        ->assertOk()
        ->assertSee('Makanan')
        ->assertSee('2 menu');
});

it('stores a new category', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.categories.store'), [
            'cat_name' => 'Dessert',
            'description' => 'Hidangan penutup manis',
        ])
        ->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('success');

    expect(Category::sole()->cat_name)->toBe('Dessert');
});

it('rejects a duplicate category name', function () {
    Category::factory()->create(['cat_name' => 'Minuman']);

    $this->actingAs($this->admin)
        ->post(route('admin.categories.store'), [
            'cat_name' => 'Minuman',
            'description' => 'Duplikat',
        ])
        ->assertSessionHasErrors('cat_name');

    expect(Category::count())->toBe(1);
});

it('validates the category form', function (array $payload, string $field) {
    $this->actingAs($this->admin)
        ->post(route('admin.categories.store'), $payload)
        ->assertSessionHasErrors($field);
})->with([
    'missing name' => [[['cat_name' => ''], 'description' => 'Ada'], 'cat_name'],
    'missing description' => [['cat_name' => 'Baru', 'description' => ''], 'description'],
]);

it('updates a category and lets it keep its own name', function () {
    $category = Category::factory()->create(['cat_name' => 'Makanan']);

    $this->actingAs($this->admin)
        ->put(route('admin.categories.update', $category), [
            'cat_name' => 'Makanan',
            'description' => 'Deskripsi baru',
        ])
        ->assertRedirect(route('admin.categories.index'))
        ->assertSessionHasNoErrors();

    expect($category->refresh()->description)->toBe('Deskripsi baru');
});

it('deletes an unused category', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($category);
});

it('refuses to delete a category that still has menus', function () {
    $category = Category::factory()->create(['cat_name' => 'Makanan']);
    Item::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->admin)
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('error');

    $this->assertNotSoftDeleted($category);
});

it('refuses category management to a non-admin', function () {
    $chef = User::factory()->chef()->create();

    $this->actingAs($chef)
        ->post(route('admin.categories.store'), ['cat_name' => 'X', 'description' => 'Y'])
        ->assertForbidden();
});
