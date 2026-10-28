@extends('admin.layouts.master')

@section('title', 'Daftar Pesanan')
@section('heading', 'Daftar Pesanan')
@section('subheading', 'Seluruh pesanan pelanggan beserta status pembayaran dan dapur')

@section('content')
<div class="rk-card mb-3">
    <form action="{{ route('admin.orders.index') }}" method="GET" class="p-3 row g-2 align-items-end">
        <div class="col-sm-4 col-lg-3">
            <label for="status" class="form-label small mb-1">Status Pesanan</label>
            <select id="status" name="status" class="form-select form-select-sm">
                <option value="">Semua status</option>
                @foreach ($orderStatuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-4 col-lg-3">
            <label for="payment_status" class="form-label small mb-1">Status Pembayaran</label>
            <select id="payment_status" name="payment_status" class="form-select form-select-sm">
                <option value="">Semua pembayaran</option>
                @foreach ($paymentStatuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['payment_status'] ?? null) === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-4 col-lg-3">
            <label for="date" class="form-label small mb-1">Tanggal</label>
            <input type="date" id="date" name="date" class="form-control form-control-sm"
                   value="{{ $filters['date'] ?? '' }}">
        </div>
        <div class="col-lg-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                <i class="fas fa-filter me-1"></i> Terapkan
            </button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-light border btn-sm">Reset</a>
        </div>
    </form>
</div>

<div class="rk-card">
    <div class="p-3 border-bottom">
        <h2 class="h6 mb-0">{{ $orders->count() }} pesanan ditemukan</h2>
    </div>

    <div class="table-responsive p-3">
        <table class="table table-hover align-middle w-100" data-datatable data-page-length="15">
            <thead class="table-light">
                <tr>
                    <th>Kode</th>
                    <th>Pelanggan</th>
                    <th>Meja</th>
                    <th>Item</th>
                    <th>Total</th>
                    <th>Metode</th>
                    <th>Pembayaran</th>
                    <th>Status</th>
                    <th>Waktu</th>
                    <th class="no-sort text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td class="fw-semibold">{{ $order->order_code }}</td>
                        <td>
                            <span class="d-block">{{ $order->customer_name }}</span>
                            <small class="text-body-secondary">{{ $order->customer_phone }}</small>
                        </td>
                        <td>{{ $order->table_number }}</td>
                        <td>{{ $order->orderItems->sum('quantity') }}</td>
                        <td data-order="{{ $order->grand_total }}"><x-rupiah :value="$order->grand_total" /></td>
                        <td>
                            <i class="fas {{ $order->payment_method->icon() }} me-1 text-body-secondary"></i>
                            {{ $order->payment_method->label() }}
                        </td>
                        <td><x-status-badge :status="$order->payment_status" /></td>
                        <td><x-status-badge :status="$order->status" /></td>
                        <td data-order="{{ $order->created_at?->timestamp ?? 0 }}">
                            {{ $order->created_at?->translatedFormat('d M Y H:i') ?? '-' }}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-light border" title="Detail">
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
