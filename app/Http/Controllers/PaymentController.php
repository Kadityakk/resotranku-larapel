<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Contracts\PaymentGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentGateway $gateway) {}

    /**
     * Opens the Snap popup for an unpaid online order.
     */
    public function show(Order $order): View|RedirectResponse
    {
        abort_unless($this->customerOwns($order), 404);

        if ($order->isPaid()) {
            return redirect()->route('checkout.success', $order->order_code);
        }

        $order->load(['orderItems.item']);

        try {
            $snapToken = $this->resolveSnapToken($order);
        } catch (Throwable $exception) {
            Log::error('Gagal membuat token pembayaran Midtrans.', [
                'order_code' => $order->order_code,
                'exception' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('checkout.success', $order->order_code)
                ->with('error', 'Pembayaran online sedang tidak tersedia. Silakan bayar di kasir atau coba lagi nanti.');
        }

        return view('customer.payment', [
            'order' => $order,
            'snapToken' => $snapToken,
            'clientKey' => $this->gateway->clientKey(),
            'snapUrl' => config('midtrans.snap_url'),
        ]);
    }

    /**
     * Midtrans server-to-server webhook. CSRF is exempt for this route and the
     * payload is verified by signature inside the gateway.
     */
    public function notification(Request $request): JsonResponse
    {
        try {
            $notification = $this->gateway->readNotification($request->all());
        } catch (ValidationException $exception) {
            Log::warning('Notifikasi Midtrans ditolak.', ['errors' => $exception->errors()]);

            return response()->json(['message' => 'Notifikasi tidak valid.'], 403);
        }

        $order = Order::where('order_code', $notification->orderCode)->first();

        if ($order === null) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($order->isPaid()) {
            return response()->json(['message' => 'Pesanan sudah dibayar.']);
        }

        if ($notification->isPaid()) {
            $order->markAsPaid($notification->transactionId);
        } else {
            $order->update([
                'payment_status' => $notification->paymentStatus,
                'midtrans_transaction_id' => $notification->transactionId ?? $order->midtrans_transaction_id,
            ]);
        }

        Log::info('Notifikasi Midtrans diproses.', [
            'order_code' => $order->order_code,
            'midtrans_status' => $notification->rawStatus,
            'payment_status' => $notification->paymentStatus->value,
        ]);

        return response()->json(['message' => 'Notifikasi diterima.']);
    }

    /**
     * Where the Snap popup sends the browser once it closes. The webhook is the
     * source of truth, so this only decides which message the customer sees.
     */
    public function finish(Request $request, Order $order): RedirectResponse
    {
        abort_unless($this->customerOwns($order), 404);

        $order->refresh();

        if ($order->isPaid()) {
            return redirect()
                ->route('checkout.success', $order->order_code)
                ->with('success', 'Pembayaran berhasil. Pesanan Anda sedang diproses.');
        }

        if ($order->payment_status === PaymentStatus::Unpaid) {
            return redirect()
                ->route('checkout.success', $order->order_code)
                ->with('info', 'Pembayaran sedang diverifikasi. Status pesanan akan diperbarui otomatis.');
        }

        return redirect()
            ->route('checkout.success', $order->order_code)
            ->with('error', 'Pembayaran '.$order->payment_status->label().'. Silakan coba lagi atau bayar di kasir.');
    }

    private function resolveSnapToken(Order $order): string
    {
        if (filled($order->snap_token) && $order->payment_status === PaymentStatus::Unpaid) {
            return $order->snap_token;
        }

        $snapToken = $this->gateway->createPaymentToken($order);

        $order->update([
            'snap_token' => $snapToken,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        return $snapToken;
    }

    private function customerOwns(Order $order): bool
    {
        return in_array($order->order_code, session('recent_order_codes', []), true)
            || request()->user()?->isStaff() === true;
    }
}
