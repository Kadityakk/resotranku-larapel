@extends('customer.layouts.master')

@section('title', 'Menu')
@section('page_title', 'Menu Kami')
@section('page_subtitle', 'Silakan pilih menu favorit anda')

@section('content')
<div class="container pb-5">

    @if ($latestItems->isNotEmpty())
        <section class="rk-splash" aria-labelledby="rk-splash-title">
            <div class="rk-splash__head">
                <h2 class="rk-splash__title" id="rk-splash-title">Menu Terbaru</h2>
                <p class="rk-splash__lead">Hidangan yang baru kami sajikan</p>
            </div>

            <div class="rk-splash__viewport">
                {{-- The set is laid down three times so the sliding loop never
                     exposes a gap, whatever the screen width. Only the first pass
                     is announced and reachable by keyboard; the rest decorate. --}}
                <div class="rk-splash__track">
                    @foreach (range(1, 3) as $pass)
                        <div class="rk-splash__set" aria-hidden="{{ $pass === 1 ? 'false' : 'true' }}">
                            @foreach ($latestItems as $item)
                                <a class="rk-splash__item" href="#menu-item-{{ $item->id }}"
                                   tabindex="{{ $pass === 1 ? '0' : '-1' }}">
                                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->name }}"
                                         loading="{{ $pass === 1 && $loop->first ? 'eager' : 'lazy' }}">
                                    <span class="rk-splash__flag">Baru</span>
                                    <span class="rk-splash__caption">
                                        <span class="rk-splash__name">{{ $item->name }}</span>
                                        <x-rupiah :value="$item->price" class="rk-splash__price" />
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @unless ($tableNumber)
        <div class="alert alert-warning d-flex align-items-center gap-3" role="alert">
            <i class="fa fa-qrcode fa-2x"></i>
            <div>
                <strong>Nomor meja belum terdeteksi.</strong>
                Pindai ulang QR code di meja Anda, atau isi nomor meja saat checkout.
            </div>
        </div>
    @endunless

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-5">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('menu') }}"
               class="rk-chip {{ $activeCategory ? '' : 'is-active' }}">
                Semua <sup>{{ $categories->sum('items_count') }}</sup>
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('menu', ['category' => $category->id, 'q' => $search]) }}"
                   class="rk-chip {{ $activeCategory === $category->id ? 'is-active' : '' }}">
                    {{ $category->cat_name }} <sup>{{ $category->items_count }}</sup>
                </a>
            @endforeach
        </div>

        <form action="{{ route('menu') }}" method="GET" class="rk-search" role="search">
            @if ($activeCategory)
                <input type="hidden" name="category" value="{{ $activeCategory }}">
            @endif
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari menu..." aria-label="Cari menu">
            <button type="submit" aria-label="Cari"><i class="fa fa-search"></i></button>
        </form>
    </div>

    @if ($items->isEmpty())
        <x-empty-state icon="fa-utensils" title="Menu tidak ditemukan"
                       message="Coba kata kunci lain atau pilih kategori yang berbeda.">
            <a href="{{ route('menu') }}" class="btn btn-primary rounded-pill px-4">Lihat semua menu</a>
        </x-empty-state>
    @else
        <div class="row g-4 rk-menu-grid">
            @foreach ($items as $item)
                <div class="col-sm-6 col-lg-4 col-xl-3">
                    <article class="rk-card" id="menu-item-{{ $item->id }}">
                        <div class="rk-card__plate">
                            <img src="{{ $item->imageUrl() }}" alt="{{ $item->name }}" loading="lazy">
                        </div>

                        <div class="rk-card__body">
                            <div class="rk-card__summary">
                                <div class="d-flex justify-content-between align-items-baseline gap-2">
                                    <h3 class="rk-card__name">{{ $item->name }}</h3>
                                    <x-rupiah :value="$item->price" class="rk-card__price" />
                                </div>
                                <div class="rk-tags">
                                    <span class="rk-tag">{{ $item->category?->cat_name ?? 'Lainnya' }}</span>
                                </div>
                            </div>

                            <div class="rk-card__order">
                                <p class="rk-card__desc">{{ $item->description }}</p>
                                <div class="rk-stepper">
                                    <button type="button" data-qty-step="-1" aria-label="Kurangi jumlah">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                    <input type="number" id="qty-{{ $item->id }}" value="1" min="1" max="99"
                                           aria-label="Jumlah {{ $item->name }}">
                                    <button type="button" data-qty-step="1" aria-label="Tambah jumlah">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                                <button type="button"
                                        class="rk-btn-cart"
                                        data-cart-add
                                        data-item-id="{{ $item->id }}"
                                        data-qty-source="#qty-{{ $item->id }}"
                                        data-url="{{ route('cart.add') }}">
                                    Tambah Keranjang
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
