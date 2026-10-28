<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Cashier;
use App\Http\Controllers\Chef;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer
|--------------------------------------------------------------------------
|
| Guests order by scanning the QR code on their table, which points at
| /menu?meja={number}. No account is required.
|
*/

Route::get('/', fn () => redirect()->route('menu'));

Route::get('/menu', [MenuController::class, 'index'])->name('menu');

Route::controller(MenuController::class)->group(function () {
    Route::get('/cart', 'cart')->name('cart');
    Route::post('/cart/add', 'addToCart')->name('cart.add');
    Route::post('/cart/update', 'updateCart')->name('cart.update');
    Route::post('/cart/remove', 'removeFromCart')->name('cart.remove');
    Route::delete('/cart', 'clearCart')->name('cart.clear');

    Route::get('/checkout', 'checkout')->name('checkout');
    Route::post('/checkout', 'placeOrder')->name('checkout.store');
    Route::get('/checkout/{order:order_code}/selesai', 'orderSuccess')->name('checkout.success');
});

/*
|--------------------------------------------------------------------------
| Payment Gateway (Midtrans)
|--------------------------------------------------------------------------
|
| The notification route is the server-to-server webhook and is excluded from
| CSRF in bootstrap/app.php; it verifies the Midtrans signature instead.
|
*/

Route::controller(PaymentController::class)->prefix('payment')->name('payment.')->group(function () {
    Route::post('midtrans/notification', 'notification')->name('notification');
    Route::get('{order:order_code}', 'show')->name('show');
    Route::get('{order:order_code}/finish', 'finish')->name('finish');
});

/*
|--------------------------------------------------------------------------
| Staff
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirect(AuthenticatedSessionController::homeRouteFor(request()->user())))
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::patch('items/{item}/toggle', [Admin\ItemController::class, 'toggleActive'])->name('items.toggle');
        Route::resource('items', Admin\ItemController::class);
        Route::resource('categories', Admin\CategoryController::class)->except('show');
        Route::resource('roles', Admin\RoleController::class)->except('show');
        Route::resource('employees', Admin\EmployeeController::class);

        Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.status');
    });

    Route::middleware('role:cashier,admin')->prefix('kasir')->name('cashier.')->group(function () {
        Route::get('orders', [Cashier\OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [Cashier\OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/confirm', [Cashier\OrderController::class, 'confirm'])->name('orders.confirm');
        Route::patch('orders/{order}/cancel', [Cashier\OrderController::class, 'cancel'])->name('orders.cancel');
    });

    Route::middleware('role:chef,admin')->prefix('dapur')->name('chef.')->group(function () {
        Route::get('orders', [Chef\OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [Chef\OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [Chef\OrderController::class, 'updateStatus'])->name('orders.status');
    });
});

require __DIR__.'/auth.php';
