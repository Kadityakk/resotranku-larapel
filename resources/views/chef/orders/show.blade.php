@extends('admin.layouts.master')

@section('title', 'Detail Pesanan Dapur')
@section('heading', 'Pesanan '.$order->order_code)
@section('subheading', 'Meja '.$order->table_number.' &middot; '.($order->created_at?->translatedFormat('H:i') ?? '-'))

@section('content')
<div class="row justify-content-center g-4">
    <div class="col-lg-7">
        <div class="rk-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h6 mb-0">Daftar Masakan</h2>
                <x-status-badge :status="$order->status" class="py-2 px-3" />
            </div>

            <ul class="list-group list-group-flush mb-3">
                @foreach ($order->orderItems as $orderItem)
                    <li class="list-group-item d-flex align-items-center gap-3">
                        <span class="badge rounded-pill text-bg-primary fs-6">{{ $orderItem->quantity }}&times;</span>
                        <span class="flex-grow-1">{{ $orderItem->item?->name ?? 'Menu telah dihapus' }}</span>
                    </li>
                @endforeach
            </ul>

            @if ($order->note)
                <div class="alert alert-warning mb-0">
                    <strong><i class="fas fa-note-sticky me-1"></i> Catatan pelanggan:</strong>
                    {{ $order->note }}
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="rk-card p-4">
            <h2 class="h6 mb-3">Perbarui Status</h2>

            @if ($order->status->isFinal())
                <p class="text-body-secondary small mb-0">
                    Pesanan sudah berstatus <strong>{{ $order->status->label() }}</strong>.
                </p>
            @else
                <div class="d-grid gap-2">
                    @foreach ($order->status->allowedTransitions() as $status)
                        @continue (! in_array($status, App\Enums\OrderStatus::kitchenStatuses(), true))

                        <form action="{{ route('chef.orders.status', $order) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $status->value }}">
                            <button type="submit" class="btn btn-outline-{{ $status->color() }} w-100">
                                <i class="fas {{ $status->icon() }} me-1"></i> {{ $status->label() }}
                            </button>
                        </form>
                    @endforeach
                </div>
            @endif

            <a href="{{ route('chef.orders.index') }}" class="btn btn-light border w-100 mt-3">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Papan
            </a>
        </div>
    </div>
</div>
@endsection
