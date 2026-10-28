@extends('admin.layouts.master')

@section('title', 'Detail Pesanan')
@section('heading', 'Pesanan '.$order->order_code)
@section('subheading', $order->created_at?->translatedFormat('l, d F Y H:i') ?? '-')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="rk-card p-4 mb-3">
            <div class="text-center border-bottom pb-3 mb-3">
                <h2 class="h4 mb-1">Restoranku</h2>
                <p class="text-body-secondary small mb-0">Struk Pesanan</p>
            </div>

            <dl class="row small mb-3">
                <dt class="col-5">Kode Pesanan</dt>
                <dd class="col-7 fw-semibold">{{ $order->order_code }}</dd>

                <dt class="col-5">Nomor Meja</dt>
                <dd class="col-7">{{ $order->table_number }}</dd>

                <dt class="col-5">Pelanggan</dt>
                <dd class="col-7">{{ $order->customer_name }}</dd>

                <dt class="col-5">WhatsApp</dt>
                <dd class="col-7">{{ $order->customer_phone }}</dd>

                <dt class="col-5">Metode Bayar</dt>
                <dd class="col-7">
                    <i class="fas {{ $order->payment_method->icon() }} me-1"></i>
                    {{ $order->payment_method->label() }}
                </dd>

                <dt class="col-5">Status Bayar</dt>
                <dd class="col-7"><x-status-badge :status="$order->payment_status" /></dd>

                <dt class="col-5">Status Pesanan</dt>
                <dd class="col-7"><x-status-badge :status="$order->status" /></dd>
            </dl>

            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Menu</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->orderItems as $orderItem)
                        <tr>
                            <td>
                                {{ $orderItem->item?->name ?? 'Menu telah dihapus' }}
                                <small class="d-block text-body-secondary"><x-rupiah :value="$orderItem->price" /> / porsi</small>
                            </td>
                            <td class="text-center">{{ $orderItem->quantity }}</td>
                            <td class="text-end"><x-rupiah :value="$orderItem->total_price" /></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-end">Subtotal</td>
                        <td class="text-end"><x-rupiah :value="$order->subtotal" /></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-end">Pajak</td>
                        <td class="text-end"><x-rupiah :value="$order->tax" /></td>
                    </tr>
                    <tr class="fw-bold">
                        <td colspan="2" class="text-end">Total</td>
                        <td class="text-end fs-5 text-primary"><x-rupiah :value="$order->grand_total" /></td>
                    </tr>
                </tfoot>
            </table>

            @if ($order->note)
                <div class="alert alert-light border small mb-0">
                    <strong>Catatan:</strong> {{ $order->note }}
                </div>
            @endif
        </div>

        <div class="d-flex gap-2 d-print-none">
            <a href="{{ route('cashier.orders.index') }}" class="btn btn-light border">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>

            @if ($order->canBeConfirmed())
                @php
                    $awaitingOnlinePayment = $order->payment_method->isOnline() && ! $order->isPaid();
                @endphp
                <form action="{{ route('cashier.orders.confirm', $order) }}" method="POST" class="flex-grow-1"
                      data-confirm-action
                      data-confirm-title="Konfirmasi pesanan {{ $order->order_code }}?"
                      data-confirm-text="Pesanan akan ditandai lunas dan diteruskan ke dapur.">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-primary w-100" @disabled($awaitingOnlinePayment)>
                        <i class="fas fa-circle-check me-1"></i> Konfirmasi Pembayaran
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
