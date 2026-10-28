<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

class UpdateOrderStatusAction
{
    /**
     * Moves an order along its lifecycle, rejecting transitions that would skip
     * or reverse a step.
     *
     * @throws ValidationException
     */
    public function handle(Order $order, OrderStatus $status): Order
    {
        if ($order->status === $status) {
            return $order;
        }

        if (! $order->status->canTransitionTo($status)) {
            throw ValidationException::withMessages([
                'status' => 'Pesanan berstatus "'.$order->status->label().'" tidak dapat diubah menjadi "'.$status->label().'".',
            ]);
        }

        $order->update(['status' => $status]);

        return $order;
    }
}
