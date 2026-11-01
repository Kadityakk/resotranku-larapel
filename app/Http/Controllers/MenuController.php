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
use Illuminate\Support\Collection;
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

        $activeCategory = $request->integer('category') ?: null;
        $search = $request->string('q')->value();

        $items = Item::active()
            ->with('category')
            ->when($activeCategory, fn ($query) => $query->where('category_id', $activeCategory))
            ->when(filled($search), fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->get();

        return view('customer.menu', [
            'items' => $items,
            'latestItems' => $this->latestItems($activeCategory === null && blank($search)),
            'categories' => Category::withCount(['items' => fn ($query) => $query->active()])
                ->orderBy('cat_name')
                ->get(),
            'activeCategory' => $activeCategory,
            'search' => $search,
            'tableNumber' => $this->cart->tableNumber(),
            'orderBar' => $this->orderBarData(),
        ]);
    }

    /**
     * The three most recently added menus, shown as the splash band at the top
     * of the menu. Skipped while a category or search filter is active, since a
     * hero of unrelated dishes only gets in the way of the result list.
     *
     * @return Collection<int, Item>
     */
    private function latestItems(bool $browsingEverything): Collection
    {
        if (! $browsingEverything) {
            return collect();
        }

        return Item::active()->with('category')->latest()->limit(3)->get();
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

        return $this->cartResponse($item->name.' ditambahkan ke pesanan.');
    }

    public function updateCart(UpdateCartRequest $request): JsonResponse
    {
        $itemId = (int) $request->validated('id');

        if ($this->cart->line($itemId) === null) {
            return response()->json([
                'success' => false,
                'message' => 'Menu ini belum ada di pesanan Anda.',
            ], 422);
        }

        if ($request->hasNote()) {
            $this->cart->setNote($itemId, $request->validated('note'));
        }

        if ($request->has('qty')) {
            $this->cart->setQuantity($itemId, (int) $request->validated('qty'));
        }

        return $this->cartResponse('Pesanan berhasil diperbarui.');
    }

    public function removeFromCart(RemoveFromCartRequest $request): JsonResponse
    {
        if (! $this->cart->remove((int) $request->validated('id'))) {
            return response()->json([
                'success' => false,
                'message' => 'Menu ini belum ada di pesanan Anda.',
            ], 422);
        }

        return $this->cartResponse('Menu dihapus dari pesanan.');
    }

    public function checkout(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('menu')->with('error', 'Belum ada menu yang dipilih. Silakan pilih menu terlebih dahulu.');
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

    /**
     * Everything the order bar partial renders.
     *
     * @return array{lines: array<int, array{id: int, name: string, price: int, image: string, qty: int, note: ?string}>, totals: array{subtotal: int, tax: int, grand_total: int}, quantity: int, taxRate: float}
     */
    private function orderBarData(): array
    {
        return [
            'lines' => $this->cart->all(),
            'totals' => $this->cart->totals(),
            'quantity' => $this->cart->totalQuantity(),
            'taxRate' => $this->cart->taxRate(),
        ];
    }

    /**
     * The order bar is re-rendered on the server after every change, so its
     * markup lives in one Blade partial instead of being rebuilt in JavaScript.
     */
    private function cartResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'cart_quantity' => $this->cart->totalQuantity(),
            'totals' => $this->cart->totals(),
            'order_bar' => view('customer.partials.order-bar', ['orderBar' => $this->orderBarData()])->render(),
        ]);
    }
}
