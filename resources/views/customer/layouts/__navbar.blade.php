<div class="container-fluid fixed-top">
    <div class="container px-0">
        <nav class="navbar navbar-light bg-white navbar-expand-xl">
            <a href="{{ route('menu') }}" class="navbar-brand"><h1 class="text-primary display-6 mb-0">Restoranku</h1></a>
            <button class="navbar-toggler py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars text-primary"></span>
            </button>
            <div class="collapse navbar-collapse bg-white" id="navbarCollapse">
                <div class="navbar-nav mx-auto">
                    <a href="{{ route('menu') }}" class="nav-item nav-link {{ request()->routeIs('menu') ? 'active' : '' }}">Menu</a>
                    <a href="{{ route('cart') }}" class="nav-item nav-link {{ request()->routeIs('cart') ? 'active' : '' }}">Keranjang</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="nav-item nav-link">Dasbor Staf</a>
                    @endauth
                </div>
                <div class="d-flex align-items-center gap-3 m-3 me-0">
                    @if ($tableNumber ?? null)
                        <span class="badge rounded-pill text-bg-secondary py-2 px-3">
                            <i class="fa fa-chair me-1"></i> Meja {{ $tableNumber }}
                        </span>
                    @endif
                    <a href="{{ route('cart') }}" class="position-relative my-auto text-dark" aria-label="Keranjang">
                        <i class="fa fa-shopping-bag fa-2x"></i>
                        <span id="cart-badge"
                              class="badge rounded-pill text-bg-primary cart-badge {{ ($cartQuantity ?? 0) > 0 ? '' : 'd-none' }}">
                            {{ $cartQuantity ?? 0 }}
                        </span>
                    </a>
                </div>
            </div>
        </nav>
    </div>
</div>

<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6">@yield('page_title', 'Menu Kami')</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item active text-primary">@yield('page_subtitle', 'Silakan pilih menu favorit anda')</li>
    </ol>
</div>
