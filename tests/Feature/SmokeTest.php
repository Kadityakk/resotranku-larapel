<?php

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Password;

it('renders every guest page', function (string $url) {
    $this->get($url)->assertOk();
})->with([
    'menu' => '/menu',
    'menu with table' => '/menu?meja=3',
    'menu filtered' => '/menu?q=nasi',
    'cart' => '/cart',
    'login' => '/login',
    'register' => '/register',
    'forgot password' => '/forgot-password',
]);

it('renders the password reset page', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $this->get('/reset-password/'.$token.'?email='.urlencode($user->email))->assertOk();
});

it('renders the password confirmation page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('password.confirm'))
        ->assertOk();
});

it('renders the email verification notice', function () {
    $this->actingAs(User::factory()->unverified()->create())
        ->get(route('verification.notice'))
        ->assertOk();
});

it('renders every admin page', function (string $routeName) {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $item = Item::factory()->create(['category_id' => $category->id]);
    $order = Order::factory()->create();
    OrderItem::factory()->create(['order_id' => $order->id, 'item_id' => $item->id]);

    $parameters = match (true) {
        str_contains($routeName, 'items.') => ['item' => $item],
        str_contains($routeName, 'categories.') => ['category' => $category],
        str_contains($routeName, 'roles.') => ['role' => $admin->role],
        str_contains($routeName, 'employees.') => ['employee' => $admin],
        str_contains($routeName, 'orders.') => ['order' => $order],
        default => [],
    };

    $this->actingAs($admin)->get(route($routeName, $parameters))->assertOk();
})->with([
    'admin.dashboard',
    'admin.items.index',
    'admin.items.create',
    'admin.items.show',
    'admin.items.edit',
    'admin.categories.index',
    'admin.categories.create',
    'admin.categories.edit',
    'admin.roles.index',
    'admin.roles.create',
    'admin.roles.edit',
    'admin.employees.index',
    'admin.employees.create',
    'admin.employees.show',
    'admin.employees.edit',
    'admin.orders.index',
    'admin.orders.show',
    'profile.edit',
]);

it('renders the till pages', function () {
    $cashier = User::factory()->cashier()->create();
    $order = Order::factory()->create();
    OrderItem::factory()->create(['order_id' => $order->id]);

    $this->actingAs($cashier)->get(route('cashier.orders.index'))->assertOk();
    $this->actingAs($cashier)->get(route('cashier.orders.show', $order))->assertOk();
});

it('renders the kitchen pages', function () {
    $chef = User::factory()->chef()->create();
    $order = Order::factory()->status(OrderStatus::Confirmed)->create();
    OrderItem::factory()->create(['order_id' => $order->id]);

    $this->actingAs($chef)->get(route('chef.orders.index'))->assertOk();
    $this->actingAs($chef)->get(route('chef.orders.show', $order))->assertOk();
});

it('renders an order detail at every stage of its lifecycle', function (OrderStatus $status) {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->status($status)->create();
    OrderItem::factory()->create(['order_id' => $order->id]);

    $this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk();
})->with([
    'pending' => [OrderStatus::Pending],
    'confirmed' => [OrderStatus::Confirmed],
    'cooking' => [OrderStatus::Cooking],
    'cooked' => [OrderStatus::Cooked],
    'served' => [OrderStatus::Served],
    'cancelled' => [OrderStatus::Cancelled],
]);

it('renders a menu whose image is an uploaded file rather than a url', function () {
    $item = Item::factory()->create(['img' => 'items/foto.jpg']);

    $this->get('/menu')->assertOk()->assertSee('storage/items/foto.jpg', false);
});

it('renders a menu that has no image at all', function () {
    Item::factory()->create(['img' => null]);

    $this->get('/menu')->assertOk();
});

it('renders an order whose menu has since been archived', function () {
    $admin = User::factory()->admin()->create();
    $item = Item::factory()->create();
    $order = Order::factory()->create();
    OrderItem::factory()->create(['order_id' => $order->id, 'item_id' => $item->id]);

    $item->delete();

    $this->actingAs($admin)
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee('Menu telah dihapus');
});

it('will not let the database erase a menu that an order refers to', function () {
    $item = Item::factory()->create();
    OrderItem::factory()->create(['item_id' => $item->id]);

    expect(fn () => $item->forceDelete())
        ->toThrow(QueryException::class);
});

it('renders the menu page when no menus exist yet', function () {
    $this->get('/menu')->assertOk()->assertSee('Menu tidak ditemukan');
});

it('renders every admin index page while the tables are empty', function (string $routeName) {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route($routeName))->assertOk();
})->with([
    'admin.items.index',
    'admin.categories.index',
    'admin.orders.index',
    'admin.dashboard',
]);
