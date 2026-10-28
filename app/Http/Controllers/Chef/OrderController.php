<?php

namespace App\Http\Controllers\Chef;

use App\Actions\UpdateOrderStatusAction;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Kitchen board, oldest first so the queue is first-in first-out.
     */
    public function index(): View
    {
        $orders = Order::inKitchen()->with('orderItems.item')->oldest()->get();

        return view('chef.orders.index', [
            'queues' => [
                OrderStatus::Confirmed->value => $orders->where('status', OrderStatus::Confirmed),
                OrderStatus::Cooking->value => $orders->where('status', OrderStatus::Cooking),
                OrderStatus::Cooked->value => $orders->where('status', OrderStatus::Cooked),
            ],
            'servedToday' => Order::where('status', OrderStatus::Served)->whereDate('updated_at', today())->count(),
        ]);
    }

    public function show(Order $order): View
    {
        return view('chef.orders.show', [
            'order' => $order->load(['orderItems.item']),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order, UpdateOrderStatusAction $updateStatus): RedirectResponse
    {
        $status = $request->status();

        abort_unless(in_array($status, OrderStatus::kitchenStatuses(), true), 403, 'Dapur tidak dapat mengubah pesanan ke status tersebut.');

        $updateStatus->handle($order, $status);

        return back()->with('success', 'Pesanan '.$order->order_code.' diperbarui menjadi "'.$order->status->label().'".');
    }
}
