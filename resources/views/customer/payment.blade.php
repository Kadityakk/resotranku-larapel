@extends('customer.layouts.master')

@section('title', 'Pembayaran')
@section('page_title', 'Pembayaran')
@section('page_subtitle', 'Selesaikan pembayaran untuk meneruskan pesanan ke dapur')

@section('content')
<div class="container-fluid">
    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="bg-light rounded p-4 text-center">
                    <i class="fas fa-qrcode fa-3x text-primary mb-3"></i>
                    <h3 class="mb-1">{{ $order->order_code }}</h3>
                    <p class="text-body-secondary">Meja {{ $order->table_number }} &middot; {{ $order->customer_name }}</p>

                    <div class="py-3 my-3 border-top border-bottom">
                        <small class="text-body-secondary d-block">Total Pembayaran</small>
                        <span class="display-6 fw-bold text-primary"><x-rupiah :value="$order->grand_total" /></span>
                    </div>

                    <button type="button" class="btn btn-primary text-white py-3 px-5 w-100"
                            id="snap-pay"
                            data-token="{{ $snapToken }}"
                            data-finish-url="{{ route('payment.finish', $order->order_code) }}"
                            data-autostart>
                        <i class="fas fa-credit-card me-2"></i> Bayar Sekarang
                    </button>

                    <p class="text-danger small mt-3 mb-0" id="snap-status" role="status" aria-live="polite"></p>

                    <a href="{{ route('checkout.success', $order->order_code) }}" class="btn btn-link mt-3">
                        Bayar nanti di kasir
                    </a>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-sm align-middle">
                        <tbody>
                            @foreach ($order->orderItems as $orderItem)
                                <tr>
                                    <td>{{ $orderItem->item?->name ?? 'Menu' }} &times; {{ $orderItem->quantity }}</td>
                                    <td class="text-end"><x-rupiah :value="$orderItem->total_price" /></td>
                                </tr>
                            @endforeach
                            <tr class="fw-bold border-top">
                                <td>Total</td>
                                <td class="text-end"><x-rupiah :value="$order->grand_total" /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>
    <script src="{{ asset('assets/customer/js/payment.js') }}"></script>
@endpush
