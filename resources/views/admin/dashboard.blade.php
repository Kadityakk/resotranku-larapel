@extends('admin.layouts.master')

@section('title', 'Dasbor')
@section('heading', 'Dasbor')
@section('subheading', 'Ringkasan operasional hari ini, '.now()->translatedFormat('d F Y'))

@section('content')
<div class="row g-3 mb-4">
    @php
        $cards = [
            ['label' => 'Pendapatan Hari Ini', 'value' => 'Rp'.number_format($stats['revenue_today'], 0, ',', '.'), 'icon' => 'fa-sack-dollar', 'tone' => 'success'],
            ['label' => 'Pesanan Hari Ini', 'value' => $stats['orders_today'], 'icon' => 'fa-receipt', 'tone' => 'primary'],
            ['label' => 'Menunggu Kasir', 'value' => $stats['orders_pending'], 'icon' => 'fa-hourglass-half', 'tone' => 'warning'],
            ['label' => 'Sedang di Dapur', 'value' => $stats['orders_in_kitchen'], 'icon' => 'fa-fire-burner', 'tone' => 'info'],
        ];
    @endphp

    @foreach ($cards as $card)
        <div class="col-sm-6 col-xl-3">
            <div class="rk-card rk-stat h-100">
                <div class="rk-stat__icon text-bg-{{ $card['tone'] }}">
                    <i class="fas {{ $card['icon'] }}"></i>
                </div>
                <div class="min-w-0">
                    <p class="rk-stat__value text-truncate">{{ $card['value'] }}</p>
                    <p class="rk-stat__label">{{ $card['label'] }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    @php
        $catalogCards = [
            ['label' => 'Total Menu', 'value' => $stats['items_total'], 'icon' => 'fa-burger', 'route' => route('admin.items.index')],
            ['label' => 'Menu Tidak Aktif', 'value' => $stats['items_inactive'], 'icon' => 'fa-eye-slash', 'route' => route('admin.items.index')],
            ['label' => 'Kategori', 'value' => $stats['categories_total'], 'icon' => 'fa-tags', 'route' => route('admin.categories.index')],
            ['label' => 'Karyawan', 'value' => $stats['employees_total'], 'icon' => 'fa-users', 'route' => route('admin.employees.index')],
        ];
    @endphp

    @foreach ($catalogCards as $card)
        <div class="col-6 col-xl-3">
            <a href="{{ $card['route'] }}" class="text-decoration-none">
                <div class="rk-card p-3 h-100 d-flex align-items-center justify-content-between">
                    <div>
                        <p class="rk-stat__label mb-1">{{ $card['label'] }}</p>
                        <p class="h4 mb-0 text-body">{{ $card['value'] }}</p>
                    </div>
                    <i class="fas {{ $card['icon'] }} fa-lg text-body-tertiary"></i>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="rk-card h-100">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <h2 class="h6 mb-0">Pesanan Terbaru</h2>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">Lihat semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Pelanggan</th>
                            <th>Meja</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold text-decoration-none">
                                        {{ $order->order_code }}
                                    </a>
                                    <small class="d-block text-body-secondary">{{ $order->created_at?->diffForHumans() ?? '-' }}</small>
                                </td>
                                <td>{{ $order->customer_name }}</td>
                                <td>{{ $order->table_number }}</td>
                                <td><x-rupiah :value="$order->grand_total" /></td>
                                <td><x-status-badge :status="$order->status" /></td>
                                <td><x-status-badge :status="$order->payment_status" /></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state icon="fa-receipt" title="Belum ada pesanan"
                                                   message="Pesanan pelanggan akan muncul di sini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="rk-card mb-3">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-0">Menu Terlaris</h2>
            </div>
            <ul class="list-group list-group-flush">
                @forelse ($bestSellers as $item)
                    <li class="list-group-item d-flex align-items-center gap-3">
                        <img src="{{ $item->imageUrl() }}" class="rk-thumb" style="width: 40px; height: 40px;" alt="">
                        <div class="flex-grow-1 min-w-0">
                            <span class="d-block text-truncate">{{ $item->name }}</span>
                            <small class="text-body-secondary"><x-rupiah :value="$item->price" /></small>
                        </div>
                        <span class="badge rounded-pill text-bg-light">{{ (int) $item->sold_quantity }} terjual</span>
                    </li>
                @empty
                    <li class="list-group-item text-body-secondary">Belum ada penjualan.</li>
                @endforelse
            </ul>
        </div>

        <div class="rk-card">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-0">Status Pesanan</h2>
            </div>
            <ul class="list-group list-group-flush">
                @foreach ($orderStatuses as $status)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <x-status-badge :status="$status" />
                        <span class="fw-semibold">{{ $statusBreakdown[$status->value] ?? 0 }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
