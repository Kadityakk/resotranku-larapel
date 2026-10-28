@extends('admin.layouts.master')

@section('title', 'Detail Pesanan')
@section('heading', 'Pesanan '.$order->order_code)
@section('subheading', $order->created_at?->translatedFormat('l, d F Y H:i') ?? '-')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="rk-card mb-4">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2 class="h6 mb-0">Rincian Item</h2>
                <span class="text-body-secondary small">{{ $order->orderItems->sum('quantity') }} item</span>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Menu</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Pajak</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->orderItems as $orderItem)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($orderItem->item)
                                            <img src="{{ $orderItem->item->imageUrl() }}" class="rk-thumb"
                                                 style="width: 40px; height: 40px;" alt="">
                                        @endif
                                        <span>{{ $orderItem->item?->name ?? 'Menu telah dihapus' }}</span>
                                    </div>
                                </td>
                                <td><x-rupiah :value="$orderItem->price" /></td>
                                <td>{{ $orderItem->quantity }}</td>
                                <td><x-rupiah :value="$orderItem->tax" /></td>
                                <td class="text-end"><x-rupiah :value="$orderItem->total_price" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end">Subtotal</td>
                            <td class="text-end"><x-rupiah :value="$order->subtotal" /></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end">Pajak</td>
                            <td class="text-end"><x-rupiah :value="$order->tax" /></td>
                        </tr>
                        <tr class="fw-bold">
                            <td colspan="4" class="text-end">Total</td>
                            <td class="text-end fs-5 text-primary"><x-rupiah :value="$order->grand_total" /></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if ($order->note)
            <div class="rk-card p-3">
                <h2 class="h6 mb-2"><i class="fas fa-note-sticky me-1 text-secondary"></i> Catatan Pelanggan</h2>
                <p class="mb-0">{{ $order->note }}</p>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="rk-card p-4 mb-3">
            <h2 class="h6 mb-3">Informasi Pesanan</h2>
            <dl class="row mb-0 small">
                <dt class="col-5">Kode</dt>
                <dd class="col-7 fw-semibold">{{ $order->order_code }}</dd>

                <dt class="col-5">Pelanggan</dt>
                <dd class="col-7">{{ $order->customer_name }}</dd>

                <dt class="col-5">WhatsApp</dt>
                <dd class="col-7">{{ $order->customer_phone }}</dd>

                <dt class="col-5">Nomor Meja</dt>
                <dd class="col-7">{{ $order->table_number }}</dd>

                <dt class="col-5">Metode Bayar</dt>
                <dd class="col-7">{{ $order->payment_method->label() }}</dd>

                <dt class="col-5">Status Bayar</dt>
                <dd class="col-7"><x-status-badge :status="$order->payment_status" /></dd>

                <dt class="col-5">Status Pesanan</dt>
                <dd class="col-7"><x-status-badge :status="$order->status" /></dd>

                @if ($order->paid_at)
                    <dt class="col-5">Dibayar</dt>
                    <dd class="col-7">{{ $order->paid_at->translatedFormat('d M Y H:i') }}</dd>
                @endif

                @if ($order->midtrans_transaction_id)
                    <dt class="col-5">ID Transaksi</dt>
                    <dd class="col-7"><code class="small">{{ $order->midtrans_transaction_id }}</code></dd>
                @endif

                @if ($order->user)
                    <dt class="col-5">Ditangani</dt>
                    <dd class="col-7">{{ $order->user->fullname }}</dd>
                @endif
            </dl>
        </div>

        <div class="rk-card p-4">
            <h2 class="h6 mb-3">Ubah Status Pesanan</h2>

            @if ($order->status->isFinal())
                <p class="text-body-secondary small mb-0">
                    Pesanan sudah berstatus <strong>{{ $order->status->label() }}</strong> dan tidak dapat diubah lagi.
                </p>
            @else
                <p class="text-body-secondary small">
                    Status saat ini: <strong>{{ $order->status->label() }}</strong>
                </p>
                <div class="d-grid gap-2">
                    @foreach ($order->status->allowedTransitions() as $status)
                        <form action="{{ route('admin.orders.status', $order) }}" method="POST"
                              data-confirm-action
                              data-confirm-title="Ubah status menjadi &quot;{{ $status->label() }}&quot;?"
                              data-confirm-text="Pesanan {{ $order->order_code }} akan diperbarui.">
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

            <a href="{{ route('admin.orders.index') }}" class="btn btn-light border w-100 mt-3">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
</div>
@endsection
