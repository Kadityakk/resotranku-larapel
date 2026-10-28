@php
    // The first word of the page title takes the accent colour, the rest the
    // brand green, so every page heading reads like "Menu Kami".
    [$titleLead, $titleRest] = array_pad(explode(' ', trim($__env->yieldContent('page_title', 'Menu Kami')), 2), 2, '');
@endphp

<header class="rk-nav">
    <div class="container">
        <nav class="navbar navbar-light navbar-expand-lg py-3">
            <a href="{{ route('menu') }}" class="navbar-brand rk-brand">
                <i class="fas fa-leaf"></i> Restoranku
            </a>

            <div class="d-flex align-items-center gap-3 order-lg-last ms-auto ms-lg-0">
                @if ($tableNumber ?? null)
                    <span class="rk-table-pill">
                        <i class="fa fa-chair me-1"></i> Meja {{ $tableNumber }}
                    </span>
                @endif
                <a href="{{ route('cart') }}" class="rk-icon-btn position-relative" aria-label="Keranjang">
                    <i class="fa fa-shopping-bag"></i>
                    <span id="cart-badge"
                          class="badge rounded-pill cart-badge {{ ($cartQuantity ?? 0) > 0 ? '' : 'd-none' }}">
                        {{ $cartQuantity ?? 0 }}
                    </span>
                </a>
                <button class="navbar-toggler border-0 px-2" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarCollapse" aria-label="Buka navigasi">
                    <span class="fa fa-bars"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav mx-auto gap-lg-5">
                    <a href="{{ route('menu') }}" class="nav-item nav-link {{ request()->routeIs('menu') ? 'active' : '' }}">Menu</a>
                    <a href="{{ route('cart') }}" class="nav-item nav-link {{ request()->routeIs('cart') ? 'active' : '' }}">Keranjang</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="nav-item nav-link">Dasbor Staf</a>
                    @endauth
                </div>
            </div>
        </nav>
    </div>
</header>

<section class="rk-page-head">
    <svg class="rk-page-head__art" viewBox="0 0 320 120" aria-hidden="true">
        <path d="M20 70c40-30 90-38 140-20s90 20 130-10" />
        <path d="M150 52c-6-18 2-34 18-40 4 16-2 32-18 40z" />
        <path d="M190 58c10-14 28-18 42-10-10 14-28 18-42 10z" />
        <circle cx="110" cy="80" r="16" />
        <circle cx="110" cy="80" r="8" />
        <path d="M240 40c14 4 26 16 28 32-16-2-28-14-28-32z" />
    </svg>
    <div class="container">
        <h1 class="rk-page-title">
            <span class="rk-page-title__lead">{{ $titleLead }}</span>
            @if ($titleRest !== '')
                <span>{{ $titleRest }}</span>
            @endif
        </h1>
        <p class="rk-page-subtitle mb-0">@yield('page_subtitle', 'Silakan pilih menu favorit anda')</p>
    </div>
</section>
