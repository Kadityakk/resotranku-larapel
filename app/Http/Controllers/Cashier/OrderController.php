<?php

namespace App\Http\Controllers\Cashier;

use App\Actions\ConfirmOrderAction;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * The till queue: orders waiting to be confirmed, plus today's history so a
     * cashier can look a receipt back up.
     */
    public function index(Request $request): View
    {
        return view('cashier.orders.index', [
            'pendingOrders' => Order::awaitingConfirmation()->with('orderItems')->oldest()->get(),
            'processedOrders' => Order::query()
                ->with('orderItems')
                ->whereNot('status', OrderStatus::Pending)
                ->whereDate('created_at', $request->date('date') ?? today())
                ->latest()
                ->get(),
            'date' => ($request->date('date') ?? today())->toDateString(),
        ]);
    }

    public function show(Order $order): View
    {
        return view('cashier.orders.show', [
            'order' => $order->load(['orderItems.item']),
        ]);
    }

    public function confirm(Order $order, ConfirmOrderAction $confirmOrder): RedirectResponse
    {
        $confirmOrder->handle($order, request()->user());

        return redirect()
            ->route('cashier.orders.index')
            ->with('success', 'Pesanan '.$order->order_code.' dikonfirmasi dan diteruskan ke dapur.');
    }

    public function cancel(Order $order): RedirectResponse
    {
        abort_unless($order->canBeConfirmed(), 422, 'Pesanan ini sudah diproses.');

        $order->update(['status' => OrderStatus::Cancelled]);

        return redirect()
            ->route('cashier.orders.index')
            ->with('success', 'Pesanan '.$order->order_code.' dibatalkan.');
    }
}
