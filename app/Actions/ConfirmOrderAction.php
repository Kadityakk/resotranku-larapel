<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConfirmOrderAction
{
    /**
     * A cashier confirming an order also settles a cash payment at the till.
     * Online orders must already be paid before they can be confirmed.
     *
     * @throws ValidationException
     */
    public function handle(Order $order, User $cashier): Order
    {
        if (! $order->canBeConfirmed()) {
            throw ValidationException::withMessages([
                'order' => 'Pesanan ini sudah dikonfirmasi sebelumnya.',
            ]);
        }

        if ($order->payment_method->isOnline() && ! $order->isPaid()) {
            throw ValidationException::withMessages([
                'order' => 'Pembayaran online untuk pesanan ini belum diterima, sehingga belum dapat dikonfirmasi.',
            ]);
        }

        return DB::transaction(function () use ($order, $cashier): Order {
            if (! $order->isPaid()) {
                $order->markAsPaid();
            }

            $order->update([
                'status' => OrderStatus::Confirmed,
                'user_id' => $order->user_id ?? $cashier->id,
            ]);

            return $order->refresh();
        });
    }
}
