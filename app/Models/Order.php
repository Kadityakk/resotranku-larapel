<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_code',
        'user_id',
        'customer_name',
        'customer_phone',
        'subtotal',
        'tax',
        'grand_total',
        'status',
        'table_number',
        'payment_method',
        'payment_status',
        'snap_token',
        'midtrans_transaction_id',
        'paid_at',
        'note',
    ];

    protected $attributes = [
        'status' => OrderStatus::Pending->value,
        'payment_status' => PaymentStatus::Unpaid->value,
        'payment_method' => PaymentMethod::Cash->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'subtotal' => 'integer',
            'tax' => 'integer',
            'grand_total' => 'integer',
            'table_number' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Human readable, collision-safe code shown to the customer and the kitchen.
     */
    public static function generateOrderCode(): string
    {
        return 'ORD-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
    }

    #[Scope]
    protected function paid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatus::Paid);
    }

    #[Scope]
    protected function awaitingConfirmation(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Pending);
    }

    #[Scope]
    protected function inKitchen(Builder $query): Builder
    {
        return $query->whereIn('status', OrderStatus::activeKitchenValues());
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Paid;
    }

    public function canBeConfirmed(): bool
    {
        return $this->status === OrderStatus::Pending;
    }

    public function markAsPaid(?string $transactionId = null): bool
    {
        return $this->update([
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
            'midtrans_transaction_id' => $transactionId ?? $this->midtrans_transaction_id,
        ]);
    }
}
