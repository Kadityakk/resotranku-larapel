<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'revenue_today' => (int) Order::paid()->whereDate('paid_at', today())->sum('grand_total'),
                'orders_today' => Order::whereDate('created_at', today())->count(),
                'orders_pending' => Order::awaitingConfirmation()->count(),
                'orders_in_kitchen' => Order::inKitchen()->count(),
                'items_total' => Item::count(),
                'items_inactive' => Item::where('is_active', false)->count(),
                'categories_total' => Category::count(),
                'employees_total' => User::staff()->count(),
            ],
            'recentOrders' => Order::latest()->limit(8)->get(),
            'bestSellers' => Item::query()
                ->withSum(['orderItems as sold_quantity' => fn ($query) => $query->whereHas(
                    'order',
                    fn ($order) => $order->where('payment_status', PaymentStatus::Paid)
                )], 'quantity')
                ->orderByDesc('sold_quantity')
                ->limit(5)
                ->get(),
            'statusBreakdown' => Order::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'orderStatuses' => OrderStatus::cases(),
        ]);
    }
}
