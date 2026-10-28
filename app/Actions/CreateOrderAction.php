<?php

namespace App\Actions;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Services\CartService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateOrderAction
{
    public function __construct(private readonly CartService $cart) {}

    /**
     * Turns the session cart into a stored order. Prices and availability are
     * re-read from the database so a stale cart can never set the amount a
     * customer is charged.
     *
     * @param  array{customer_name: string, customer_phone: string, table_number: int, payment_method: string, note?: ?string}  $data
     *
     * @throws ValidationException
     */
    public function handle(array $data): Order
    {
        $lines = $this->cart->all();

        if ($lines === []) {
            throw ValidationException::withMessages([
                'cart' => 'Keranjang kosong. Silakan tambahkan menu sebelum checkout.',
            ]);
        }

        $items = $this->cart->freshItems();
        $unavailable = array_diff(array_keys($lines), $items->keys()->all());

        if ($unavailable !== []) {
            throw ValidationException::withMessages([
                'cart' => 'Beberapa menu di keranjang sudah tidak tersedia. Silakan periksa kembali keranjang Anda.',
            ]);
        }

        return DB::transaction(function () use ($data, $lines, $items): Order {
            $order = Order::create([
                'order_code' => Order::generateOrderCode(),
                'user_id' => auth()->id(),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'table_number' => $data['table_number'],
                'payment_method' => $data['payment_method'],
                'note' => $data['note'] ?? null,
                'subtotal' => 0,
                'tax' => 0,
                'grand_total' => 0,
            ]);

            $subtotal = 0;
            $tax = 0;

            foreach ($lines as $itemId => $line) {
                $item = $items[$itemId];
                $quantity = max(1, (int) $line['qty']);
                $lineSubtotal = $item->price * $quantity;
                $lineTax = $this->cart->lineTax($lineSubtotal);

                $order->orderItems()->create([
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'price' => $item->price,
                    'tax' => $lineTax,
                    'total_price' => $lineSubtotal + $lineTax,
                ]);

                $subtotal += $lineSubtotal;
                $tax += $lineTax;
            }

            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'grand_total' => $subtotal + $tax,
            ]);

            return $order;
        });
    }

    public function isOnlinePayment(string $paymentMethod): bool
    {
        return PaymentMethod::from($paymentMethod)->isOnline();
    }
}
