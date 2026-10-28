@extends('admin.layouts.master')

@section('title', 'Konfirmasi Pesanan')
@section('heading', 'Konfirmasi Pesanan')
@section('subheading', 'Terima pembayaran lalu teruskan pesanan ke dapur')

@section('content')
<div data-auto-refresh="30"></div>

<div class="rk-card mb-4">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="h6 mb-0">
            <i class="fas fa-hourglass-half text-warning me-1"></i>
            Menunggu Konfirmasi
            <span class="badge rounded-pill text-bg-warning ms-1">{{ $pendingOrders->count() }}</span>
        </h2>
        <small class="text-body-secondary">Halaman menyegarkan otomatis setiap 30 detik</small>
    </div>

    @if ($pendingOrders->isEmpty())
        <x-empty-state icon="fa-circle-check" title="Tidak ada pesanan menunggu"
                       message="Semua pesanan sudah dikonfirmasi. Kerja bagus!" />
    @else
        <div class="row g-3 p-3">
            @foreach ($pendingOrders as $order)
                @php
                    $awaitingOnlinePayment = $order->payment_method->isOnline() && ! $order->isPaid();
                    $confirmText = $awaitingOnlinePayment
                        ? 'Pesanan akan diteruskan ke dapur.'
                        : 'Pembayaran tunai akan dicatat sebagai lunas dan pesanan diteruskan ke dapur.';
                @endphp

                <div class="col-md-6 col-xl-4">
                    <div class="rk-ticket h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="rk-ticket__code d-block">{{ $order->order_code }}</span>
                                <small class="text-body-secondary">{{ $order->created_at?->diffForHumans() ?? '-' }}</small>
                            </div>
                            <span class="badge rounded-pill text-bg-dark">Meja {{ $order->table_number }}</span>
                        </div>

                        <p class="mb-1 small">
                            <i class="fas fa-user me-1 text-body-secondary"></i> {{ $order->customer_name }}
                        </p>
                        <p class="mb-2 small">
                            <i class="fas {{ $order->payment_method->icon() }} me-1 text-body-secondary"></i>
                            {{ $order->payment_method->label() }}
                            <x-status-badge :status="$order->payment_status" class="ms-1" />
                        </p>

                        <ul class="list-unstyled small mb-3 flex-grow-1">
                            @foreach ($order->orderItems as $orderItem)
                                <li class="d-flex justify-content-between">
                                    <span>{{ $orderItem->item?->name ?? 'Menu' }} &times; {{ $orderItem->quantity }}</span>
                                    <span><x-rupiah :value="$orderItem->total_price" /></span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="d-flex justify-content-between align-items-center border-top pt-2 mb-3">
                            <span class="fw-semibold">Total</span>
                            <span class="fw-bold fs-5 text-primary"><x-rupiah :value="$order->grand_total" /></span>
                        </div>

                        @if ($awaitingOnlinePayment)
                            <div class="alert alert-warning py-2 small mb-2">
                                Pembayaran online belum diterima, belum dapat dikonfirmasi.
                            </div>
                        @endif

                        <div class="d-flex gap-2">
                            <form action="{{ route('cashier.orders.confirm', $order) }}" method="POST" class="flex-grow-1"
                                  data-confirm-action
                                  data-confirm-title="Konfirmasi pesanan {{ $order->order_code }}?"
                                  data-confirm-text="{{ $confirmText }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-primary btn-sm w-100" @disabled($awaitingOnlinePayment)>
                                    <i class="fas fa-circle-check me-1"></i> Konfirmasi
                                </button>
                            </form>
                            <a href="{{ route('cashier.orders.show', $order) }}" class="btn btn-light border btn-sm" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form action="{{ route('cashier.orders.cancel', $order) }}" method="POST"
                                  data-confirm-delete
                                  data-confirm-title="Batalkan pesanan ini?"
                                  data-confirm-text="Pesanan {{ $order->order_code }} akan ditandai dibatalkan.">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-light border btn-sm text-danger" title="Batalkan">
                                    <i class="fas fa-xmark"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="rk-card">
    <form action="{{ route('cashier.orders.index') }}" method="GET"
          class="p-3 border-bottom d-flex justify-content-between align-items-end flex-wrap gap-2">
        <h2 class="h6 mb-0">Riwayat Pesanan</h2>
        <div class="d-flex gap-2 align-items-end">
            <div>
                <label for="date" class="form-label small mb-1">Tanggal</label>
                <input type="date" id="date" name="date" value="{{ $date }}" class="form-control form-control-sm">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
        </div>
    </form>

    <div class="table-responsive p-3">
        <table class="table table-hover align-middle w-100" data-datatable>
            <thead class="table-light">
                <tr>
                    <th>Kode</th>
                    <th>Pelanggan</th>
                    <th>Meja</th>
                    <th>Total</th>
                    <th>Pembayaran</th>
                    <th>Status</th>
                    <th>Waktu</th>
                    <th class="no-sort text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($processedOrders as $order)
                    <tr>
                        <td class="fw-semibold">{{ $order->order_code }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->table_number }}</td>
                        <td data-order="{{ $order->grand_total }}"><x-rupiah :value="$order->grand_total" /></td>
                        <td><x-status-badge :status="$order->payment_status" /></td>
                        <td><x-status-badge :status="$order->status" /></td>
                        <td data-order="{{ $order->created_at?->timestamp ?? 0 }}">{{ $order->created_at?->format('H:i') ?? '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('cashier.orders.show', $order) }}" class="btn btn-sm btn-light border" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
