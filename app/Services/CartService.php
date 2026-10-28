<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Support\Collection;

/**
 * Owns the session shopping cart and every money calculation derived from it,
 * so the cart page, the checkout page and the stored order always agree.
 *
 * @phpstan-type CartLine array{id: int, name: string, price: int, image: string, qty: int}
 */
class CartService
{
    public const SESSION_KEY = 'cart';

    public const TABLE_SESSION_KEY = 'table_number';

    /**
     * @return array<int, CartLine>
     */
    public function all(): array
    {
        return session(self::SESSION_KEY, []);
    }

    public function isEmpty(): bool
    {
        return $this->all() === [];
    }

    /**
     * Total number of pieces in the cart, used for the navbar badge.
     */
    public function totalQuantity(): int
    {
        return (int) collect($this->all())->sum('qty');
    }

    public function distinctItemCount(): int
    {
        return count($this->all());
    }

    /**
     * @return CartLine|null
     */
    public function line(int $itemId): ?array
    {
        return $this->all()[$itemId] ?? null;
    }

    /**
     * @return array<int, CartLine>
     */
    public function add(Item $item, int $quantity = 1): array
    {
        $cart = $this->all();
        $existingQuantity = $cart[$item->id]['qty'] ?? 0;

        $cart[$item->id] = [
            'id' => $item->id,
            'name' => $item->name,
            'price' => $item->price,
            'image' => $item->imageUrl(),
            'qty' => $existingQuantity + $quantity,
        ];

        return $this->put($cart);
    }

    /**
     * Sets an absolute quantity. A quantity of zero or less removes the line.
     */
    public function setQuantity(int $itemId, int $quantity): bool
    {
        $cart = $this->all();

        if (! isset($cart[$itemId])) {
            return false;
        }

        if ($quantity <= 0) {
            return $this->remove($itemId);
        }

        $cart[$itemId]['qty'] = $quantity;
        $this->put($cart);

        return true;
    }

    public function remove(int $itemId): bool
    {
        $cart = $this->all();

        if (! isset($cart[$itemId])) {
            return false;
        }

        unset($cart[$itemId]);
        $this->put($cart);

        return true;
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function taxRate(): float
    {
        return (float) config('restaurant.tax_rate');
    }

    /**
     * Rupiah has no sub-unit in practice, so every line is rounded to a whole
     * number and the order totals are summed from those rounded lines.
     */
    public function lineTax(int $lineSubtotal): int
    {
        return (int) round($lineSubtotal * $this->taxRate());
    }

    /**
     * @return array{subtotal: int, tax: int, grand_total: int}
     */
    public function totals(): array
    {
        $subtotal = 0;
        $tax = 0;

        foreach ($this->all() as $line) {
            $lineSubtotal = (int) $line['price'] * (int) $line['qty'];
            $subtotal += $lineSubtotal;
            $tax += $this->lineTax($lineSubtotal);
        }

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'grand_total' => $subtotal + $tax,
        ];
    }

    /**
     * Fresh copies of every item still in the cart, keyed by id. Prices are
     * re-read here so a stale session price never reaches a stored order.
     *
     * @return Collection<int, Item>
     */
    public function freshItems(): Collection
    {
        $ids = array_keys($this->all());

        if ($ids === []) {
            return collect();
        }

        return Item::active()->findMany($ids)->keyBy('id');
    }

    public function tableNumber(): ?int
    {
        $tableNumber = session(self::TABLE_SESSION_KEY);

        return $tableNumber === null ? null : (int) $tableNumber;
    }

    public function setTableNumber(int $tableNumber): void
    {
        session()->put(self::TABLE_SESSION_KEY, $tableNumber);
    }

    /**
     * @param  array<int, CartLine>  $cart
     * @return array<int, CartLine>
     */
    private function put(array $cart): array
    {
        session()->put(self::SESSION_KEY, $cart);

        return $cart;
    }
}
