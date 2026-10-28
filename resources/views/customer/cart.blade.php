@extends('customer.layouts.master')

@section('title', 'Keranjang')
@section('page_title', 'Keranjang Saya')
@section('page_subtitle', 'Periksa pesanan Anda sebelum melanjutkan ke pembayaran')

@section('content')
<div class="container-fluid py-5">
    <div class="container py-5">

        @if (empty($cart))
            <x-empty-state icon="fa-shopping-bag" title="Keranjang Anda masih kosong"
                           message="Tambahkan menu favorit Anda terlebih dahulu.">
                <a href="{{ route('menu') }}" class="btn btn-primary text-white rounded-pill px-4">Lihat Menu</a>
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Gambar</th>
                            <th scope="col">Menu</th>
                            <th scope="col">Harga</th>
                            <th scope="col">Jumlah</th>
                            <th scope="col">Total</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cart as $item)
                            <tr data-cart-line="{{ $item['id'] }}" data-price="{{ $item['price'] }}">
                                <th scope="row">
                                    <img src="{{ $item['image'] }}" class="img-fluid rounded-circle"
                                         style="width: 80px; height: 80px; object-fit: cover;" alt="{{ $item['name'] }}">
                                </th>
                                <td>
                                    <p class="mb-0 fw-semibold">{{ $item['name'] }}</p>
                                </td>
                                <td>
                                    <p class="mb-0"><x-rupiah :value="$item['price']" /></p>
                                </td>
                                <td>
                                    <div class="input-group quantity" style="width: 120px;">
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-sm btn-minus rounded-circle bg-light border"
                                                    data-cart-step="-1"
                                                    data-item-id="{{ $item['id'] }}"
                                                    data-url="{{ route('cart.update') }}"
                                                    aria-label="Kurangi jumlah">
                                                <i class="fa fa-minus"></i>
                                            </button>
                                        </div>
                                        <input type="number" min="0" max="99"
                                               class="form-control form-control-sm text-center border-0"
                                               value="{{ $item['qty'] }}"
                                               data-qty-input
                                               data-url="{{ route('cart.update') }}"
                                               aria-label="Jumlah {{ $item['name'] }}">
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-sm btn-plus rounded-circle bg-light border"
                                                    data-cart-step="1"
                                                    data-item-id="{{ $item['id'] }}"
                                                    data-url="{{ route('cart.update') }}"
                                                    aria-label="Tambah jumlah">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="mb-0 fw-semibold" data-line-total>
                                        Rp{{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}
                                    </p>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-md rounded-circle bg-light border"
                                            data-cart-remove
                                            data-item-id="{{ $item['id'] }}"
                                            data-url="{{ route('cart.remove') }}"
                                            aria-label="Hapus {{ $item['name'] }}">
                                        <i class="fa fa-times text-danger"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <a href="{{ route('menu') }}" class="btn border-secondary py-3 px-4 text-primary text-uppercase">
                    <i class="fa fa-arrow-left me-2"></i> Tambah Menu Lain
                </a>
                <form action="{{ route('cart.clear') }}" method="POST"
                      onsubmit="return confirm('Apakah anda yakin ingin mengosongkan keranjang?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn border-secondary py-3 px-4 text-danger text-uppercase">
                        <i class="fa fa-trash me-2"></i> Kosongkan Keranjang
                    </button>
                </form>
            </div>

            <div class="row g-4 justify-content-end mt-1">
                <div class="col-sm-8 col-md-7 col-lg-6 col-xl-4">
                    <div class="bg-light rounded">
                        <div class="p-4">
                            <h2 class="display-6 mb-4">Total <span class="fw-normal">Pesanan</span></h2>
                            <div class="d-flex justify-content-between mb-4">
                                <h5 class="mb-0 me-4">Subtotal</h5>
                                <p class="mb-0" data-summary-subtotal><x-rupiah :value="$totals['subtotal']" /></p>
                            </div>
                            <div class="d-flex justify-content-between">
                                <p class="mb-0 me-4">Pajak ({{ (int) round($taxRate * 100) }}%)</p>
                                <p class="mb-0" data-summary-tax><x-rupiah :value="$totals['tax']" /></p>
                            </div>
                        </div>
                        <div class="py-4 mb-4 border-top d-flex justify-content-between">
                            <h4 class="mb-0 ps-4 me-4">Total</h4>
                            <h5 class="mb-0 pe-4" data-summary-total><x-rupiah :value="$totals['grand_total']" /></h5>
                        </div>
                    </div>
                    <div class="d-grid mt-3">
                        <a href="{{ route('checkout') }}" class="btn btn-primary text-white py-3 text-uppercase">
                            Lanjut ke Pembayaran
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
