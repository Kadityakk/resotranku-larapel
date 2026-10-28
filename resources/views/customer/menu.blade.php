@extends('customer.layouts.master')

@section('title', 'Menu')
@section('page_title', 'Menu Kami')
@section('page_subtitle', 'Silakan pilih menu favorit anda')

@section('content')
<div class="container-fluid fruite py-5">
    <div class="container py-5">

        @unless ($tableNumber)
            <div class="alert alert-warning d-flex align-items-center gap-3" role="alert">
                <i class="fa fa-qrcode fa-2x"></i>
                <div>
                    <strong>Nomor meja belum terdeteksi.</strong>
                    Pindai ulang QR code di meja Anda, atau isi nomor meja saat checkout.
                </div>
            </div>
        @endunless

        <div class="row g-4 mb-4 align-items-end">
            <div class="col-lg-7">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('menu') }}"
                       class="btn rounded-pill px-3 {{ $activeCategory ? 'btn-outline-secondary' : 'btn-primary text-white' }}">
                        Semua
                    </a>
                    @foreach ($categories as $category)
                        <a href="{{ route('menu', ['category' => $category->id, 'q' => $search]) }}"
                           class="btn rounded-pill px-3 {{ $activeCategory === $category->id ? 'btn-primary text-white' : 'btn-outline-secondary' }}">
                            {{ $category->cat_name }}
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-5">
                <form action="{{ route('menu') }}" method="GET" class="d-flex gap-2">
                    @if ($activeCategory)
                        <input type="hidden" name="category" value="{{ $activeCategory }}">
                    @endif
                    <input type="search" name="q" value="{{ $search }}" class="form-control border-secondary"
                           placeholder="Cari menu..." aria-label="Cari menu">
                    <button type="submit" class="btn btn-primary text-white px-4">
                        <i class="fa fa-search"></i>
                    </button>
                </form>
            </div>
        </div>

        @if ($items->isEmpty())
            <x-empty-state icon="fa-utensils" title="Menu tidak ditemukan"
                           message="Coba kata kunci lain atau pilih kategori yang berbeda.">
                <a href="{{ route('menu') }}" class="btn btn-primary text-white rounded-pill px-4">Lihat semua menu</a>
            </x-empty-state>
        @else
            <div class="row g-4 justify-content-center">
                @foreach ($items as $item)
                    <div class="col-md-6 col-lg-6 col-xl-4">
                        <div class="rounded position-relative fruite-item h-100">
                            <div class="fruite-img">
                                <img src="{{ $item->imageUrl() }}" class="img-fluid w-100 rounded-top"
                                     alt="{{ $item->name }}" loading="lazy">
                            </div>
                            <div class="text-white px-3 py-1 rounded position-absolute
                                @class([
                                    'bg-warning' => $item->category?->cat_name === 'Makanan',
                                    'bg-info' => $item->category?->cat_name === 'Minuman',
                                    'bg-primary' => ! in_array($item->category?->cat_name, ['Makanan', 'Minuman'], true),
                                ])" style="top: 10px; left: 10px;">
                                {{ $item->category?->cat_name ?? 'Lainnya' }}
                            </div>
                            <div class="p-4 border border-secondary border-top-0 rounded-bottom">
                                <h4>{{ $item->name }}</h4>
                                <p class="text-limited">{{ $item->description }}</p>
                                <div class="d-flex justify-content-between align-items-center flex-lg-wrap gap-2">
                                    <p class="text-dark fs-5 fw-bold mb-0"><x-rupiah :value="$item->price" /></p>
                                    <button type="button"
                                            class="btn border border-secondary rounded-pill px-3 text-primary"
                                            data-cart-add
                                            data-item-id="{{ $item->id }}"
                                            data-url="{{ route('cart.add') }}">
                                        <i class="fa fa-shopping-bag me-2 text-primary"></i> Tambah Keranjang
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
