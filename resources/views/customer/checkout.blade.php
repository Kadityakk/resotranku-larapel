@extends('customer.layouts.master')

@section('title', 'Checkout')
@section('page_title', 'Detail Pembayaran')
@section('page_subtitle', 'Lengkapi data Anda, lalu pilih metode pembayaran')

@section('content')
<div class="container-fluid py-5">
    <div class="container py-5">
        <form action="{{ route('checkout.store') }}" method="POST">
            @csrf
            <div class="row g-5">
                <div class="col-md-12 col-lg-6 col-xl-6">
                    <div class="row">
                        <div class="col-md-12 col-lg-6">
                            <div class="form-item w-100">
                                <label class="form-label my-3" for="customer_name">Nama Lengkap<sup class="text-danger">*</sup></label>
                                <input type="text" id="customer_name" name="customer_name"
                                       class="form-control @error('customer_name') is-invalid @enderror"
                                       value="{{ old('customer_name') }}" required maxlength="255">
                                @error('customer_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-6">
                            <div class="form-item w-100">
                                <label class="form-label my-3" for="customer_phone">Nomor WhatsApp<sup class="text-danger">*</sup></label>
                                <input type="tel" id="customer_phone" name="customer_phone"
                                       class="form-control @error('customer_phone') is-invalid @enderror"
                                       value="{{ old('customer_phone') }}" required maxlength="20"
                                       placeholder="08xxxxxxxxxx">
                                @error('customer_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 col-lg-6">
                            <div class="form-item w-100">
                                <label class="form-label my-3" for="table_number">Nomor Meja<sup class="text-danger">*</sup></label>
                                <input type="number" id="table_number" name="table_number" min="1"
                                       max="{{ config('restaurant.table_count') }}"
                                       class="form-control @error('table_number') is-invalid @enderror"
                                       value="{{ old('table_number', $tableNumber) }}" required
                                       @readonly((bool) $tableNumber)>
                                @error('table_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if ($tableNumber)
                                    <small class="text-body-secondary">Terdeteksi dari QR code meja Anda.</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <br>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-item">
                                <label class="form-label mb-2" for="note">Catatan Pesanan</label>
                                <textarea id="note" name="note" class="form-control @error('note') is-invalid @enderror"
                                          spellcheck="false" rows="4" maxlength="500"
                                          placeholder="Contoh: tidak pakai sambal, es sedikit">{{ old('note') }}</textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="table-responsive">
                            <br>
                            <h4 class="mb-4">Detail Pesanan</h4>
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">Gambar</th>
                                        <th scope="col">Menu</th>
                                        <th scope="col">Harga</th>
                                        <th scope="col">Jumlah</th>
                                        <th scope="col">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cart as $item)
                                        <tr>
                                            <th scope="row">
                                                <img src="{{ $item['image'] }}" class="img-fluid rounded-circle"
                                                     style="width: 80px; height: 72px; object-fit: cover;"
                                                     alt="{{ $item['name'] }}">
                                            </th>
                                            <td>{{ $item['name'] }}</td>
                                            <td><x-rupiah :value="$item['price']" /></td>
                                            <td>{{ $item['qty'] }}</td>
                                            <td><x-rupiah :value="$item['price'] * $item['qty']" /></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <a href="{{ route('cart') }}" class="btn btn-sm border-secondary text-primary">
                                <i class="fa fa-pen me-1"></i> Ubah keranjang
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-lg-6 col-xl-6">
                    <div class="row g-4 align-items-center py-3">
                        <div class="col-lg-12">
                            <div class="bg-light rounded">
                                <div class="p-4">
                                    <h3 class="display-6 mb-4">Total <span class="fw-normal">Pesanan</span></h3>
                                    <div class="d-flex justify-content-between mb-4">
                                        <h5 class="mb-0 me-4">Subtotal</h5>
                                        <p class="mb-0"><x-rupiah :value="$totals['subtotal']" /></p>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <p class="mb-0 me-4">Pajak ({{ (int) round($taxRate * 100) }}%)</p>
                                        <p class="mb-0"><x-rupiah :value="$totals['tax']" /></p>
                                    </div>
                                </div>
                                <div class="py-4 mb-4 border-top border-bottom d-flex justify-content-between">
                                    <h4 class="mb-0 ps-4 me-4">Total</h4>
                                    <h5 class="mb-0 pe-4"><x-rupiah :value="$totals['grand_total']" /></h5>
                                </div>

                                <div class="p-4 pt-0">
                                    <h5 class="mb-3">Metode Pembayaran<sup class="text-danger">*</sup></h5>
                                    @error('payment_method')
                                        <div class="text-danger small mb-2">{{ $message }}</div>
                                    @enderror
                                    @foreach ($paymentMethods as $index => $method)
                                        <div class="form-check border rounded p-3 mb-2 d-flex align-items-center gap-2">
                                            <input type="radio" class="form-check-input mt-0"
                                                   id="payment_{{ $method->value }}" name="payment_method"
                                                   value="{{ $method->value }}"
                                                   @checked(old('payment_method', $paymentMethods[0]->value) === $method->value)
                                                   required>
                                            <label class="form-check-label flex-grow-1" for="payment_{{ $method->value }}">
                                                <i class="fas {{ $method->icon() }} me-2 text-primary"></i>
                                                {{ $method->label() }}
                                                <small class="d-block text-body-secondary">
                                                    {{ $method->isOnline()
                                                        ? 'Bayar sekarang lewat QRIS, e-wallet atau transfer bank.'
                                                        : 'Bayar langsung di kasir setelah pesanan dibuat.' }}
                                                </small>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary text-white py-3 text-uppercase">
                                    Konfirmasi Pesanan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
