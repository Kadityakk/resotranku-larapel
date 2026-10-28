<?php

namespace App\Http\Controllers;

use App\Actions\CreateOrderAction;
use App\Enums\PaymentMethod;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\RemoveFromCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Http\Requests\Checkout\StoreOrderRequest;
use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    /**
     * Menu list, optionally filtered by category. The `meja` query parameter is
     * what a table QR code carries, so it is remembered for the whole session.
     */
    public function index(Request $request): View
    {
        if ($request->filled('meja')) {
            $this->cart->setTableNumber((int) $request->query('meja'));
        }

        $items = Item::active()
            ->with('category')
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderBy('name')
            ->get();

        return view('customer.menu', [
            'items' => $items,
            'categories' => Category::orderBy('cat_name')->get(),
            'activeCategory' => $request->integer('category') ?: null,
            'search' => $request->string('q')->value(),
            'tableNumber' => $this->cart->tableNumber(),
        ]);
    }

    public function cart(): View
    {
        return view('customer.cart', [
            'cart' => $this->cart->all(),
            'totals' => $this->cart->totals(),
            'taxRate' => $this->cart->taxRate(),
            'tableNumber' => $this->cart->tableNumber(),
        ]);
    }

    public function addToCart(AddToCartRequest $request): JsonResponse
    {
        $item = Item::active()->find($request->validated('id'));

        if ($item === null) {
            return response()->json([
                'success' => false,
                'message' => 'Menu ini sedang tidak tersedia.',
            ], 422);
        }

        $this->cart->add($item, $request->quantity());

        return $this->cartResponse($item->name.' ditambahkan ke keranjang.');
    }

    public function updateCart(UpdateCartRequest $request): JsonResponse
    {
        $updated = $this->cart->setQuantity(
            (int) $request->validated('id'),
            (int) $request->validated('qty'),
        );

        if (! $updated) {
            return response()->json([
                'success' => false,
                'message' => 'Item tidak ada di keranjang.',
            ], 422);
        }

        return $this->cartResponse('Keranjang berhasil diperbarui.');
    }

    public function removeFromCart(RemoveFromCartRequest $request): JsonResponse
    {
        if (! $this->cart->remove((int) $request->validated('id'))) {
            return response()->json([
                'success' => false,
                'message' => 'Item tidak ada di keranjang.',
            ], 422);
        }

        return $this->cartResponse('Item berhasil dihapus dari keranjang.');
    }

    public function clearCart(): RedirectResponse
    {
        $this->cart->clear();

        return redirect()->route('cart')->with('success', 'Keranjang berhasil dikosongkan.');
    }

    public function checkout(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('menu')->with('error', 'Keranjang kosong. Silakan tambahkan menu sebelum checkout.');
        }

        return view('customer.checkout', [
            'cart' => $this->cart->all(),
            'totals' => $this->cart->totals(),
            'taxRate' => $this->cart->taxRate(),
            'tableNumber' => $this->cart->tableNumber(),
            'paymentMethods' => PaymentMethod::cases(),
        ]);
    }

    /**
     * Places the order. Cash orders go straight to the kitchen queue awaiting a
     * cashier; QRIS orders are handed to the payment gateway first.
     */
    public function placeOrder(StoreOrderRequest $request, CreateOrderAction $createOrder): RedirectResponse
    {
        $order = $createOrder->handle($request->validated());

        $this->cart->clear();
        $this->rememberOrder($order);

        if ($order->payment_method->isOnline()) {
            return redirect()->route('payment.show', $order->order_code);
        }

        return redirect()
            ->route('checkout.success', $order->order_code)
            ->with('success', 'Pesanan berhasil dibuat. Silakan lakukan pembayaran di kasir.');
    }

    public function orderSuccess(Order $order): View
    {
        abort_unless($this->canViewOrder($order), 404);

        $order->load(['orderItems.item']);

        return view('customer.order-success', [
            'order' => $order,
            'tableNumber' => $this->cart->tableNumber(),
        ]);
    }

    /**
     * Order codes are only readable by the browser that created them, or by staff.
     */
    private function canViewOrder(Order $order): bool
    {
        return in_array($order->order_code, session('recent_order_codes', []), true)
            || request()->user()?->isStaff() === true;
    }

    private function rememberOrder(Order $order): void
    {
        $codes = session('recent_order_codes', []);
        $codes[] = $order->order_code;

        session()->put('recent_order_codes', array_slice(array_unique($codes), -10));
    }

    private function cartResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'cart_quantity' => $this->cart->totalQuantity(),
            'totals' => $this->cart->totals(),
        ]);
    }
}
