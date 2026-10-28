@extends('customer.layouts.master')

@section('title', 'Pesanan Berhasil')
@section('page_title', 'Pesanan Diterima')
@section('page_subtitle', 'Terima kasih! Pesanan Anda sudah kami catat')

@section('content')
<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="text-center mb-5">
                    <i class="fas fa-circle-check fa-4x text-primary mb-3"></i>
                    <h2 class="mb-2">Pesanan berhasil dibuat</h2>
                    <p class="text-body-secondary mb-0">Tunjukkan kode pesanan di bawah kepada kasir atau pelayan kami.</p>
                </div>

                <div class="bg-light rounded p-4 mb-4">
                    <div class="row g-4 text-center text-md-start">
                        <div class="col-md-4">
                            <small class="text-body-secondary d-block">Kode Pesanan</small>
                            <span class="fs-5 fw-bold text-primary">{{ $order->order_code }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-body-secondary d-block">Nomor Meja</small>
                            <span class="fs-5 fw-bold">{{ $order->table_number }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-body-secondary d-block">Waktu Pesan</small>
                            <span class="fw-semibold">{{ $order->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-4 text-center text-md-start">
                        <div class="col-md-4">
                            <small class="text-body-secondary d-block mb-1">Status Pesanan</small>
                            <x-status-badge :status="$order->status" class="py-2 px-3" />
                        </div>
                        <div class="col-md-4">
                            <small class="text-body-secondary d-block mb-1">Status Pembayaran</small>
                            <x-status-badge :status="$order->payment_status" class="py-2 px-3" />
                        </div>
                        <div class="col-md-4">
                            <small class="text-body-secondary d-block mb-1">Metode Pembayaran</small>
                            <span class="fw-semibold">
                                <i class="fas {{ $order->payment_method->icon() }} me-1 text-primary"></i>
                                {{ $order->payment_method->label() }}
                            </span>
                        </div>
                    </div>
                </div>

                @if (! $order->isPaid() && $order->payment_method->isOnline())
                    <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <strong>Pembayaran belum selesai.</strong>
                            Selesaikan pembayaran agar pesanan Anda diteruskan ke dapur.
                        </div>
                        <a href="{{ route('payment.show', $order->order_code) }}" class="btn btn-primary text-white">
                            Bayar Sekarang
                        </a>
                    </div>
                @elseif (! $order->isPaid())
                    <div class="alert alert-info">
                        <strong>Silakan bayar di kasir.</strong>
                        Sebutkan kode pesanan <strong>{{ $order->order_code }}</strong> kepada kasir kami.
                    </div>
                @endif

                <div class="table-responsive">
                    <h4 class="mb-3">Rincian Pesanan</h4>
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Menu</th>
                                <th scope="col">Harga</th>
                                <th scope="col">Jumlah</th>
                                <th scope="col" class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->orderItems as $orderItem)
                                <tr>
                                    <td>{{ $orderItem->item?->name ?? 'Menu dihapus' }}</td>
                                    <td><x-rupiah :value="$orderItem->price" /></td>
                                    <td>{{ $orderItem->quantity }}</td>
                                    <td class="text-end"><x-rupiah :value="$orderItem->price * $orderItem->quantity" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end">Subtotal</td>
                                <td class="text-end"><x-rupiah :value="$order->subtotal" /></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end">Pajak</td>
                                <td class="text-end"><x-rupiah :value="$order->tax" /></td>
                            </tr>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Total</td>
                                <td class="text-end"><x-rupiah :value="$order->grand_total" /></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($order->note)
                    <div class="alert alert-light border">
                        <strong>Catatan Anda:</strong> {{ $order->note }}
                    </div>
                @endif

                <div class="d-flex justify-content-center gap-2 mt-4">
                    <a href="{{ route('menu') }}" class="btn btn-primary text-white rounded-pill px-4 py-3">
                        Pesan Menu Lain
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
