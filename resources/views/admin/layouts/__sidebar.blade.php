@php
    $user = auth()->user();
@endphp

<aside class="rk-sidebar">
    <div class="rk-sidebar__brand">
        <h1>Restoranku</h1>
        <small>{{ $user?->role?->role_name ?? 'Staf' }} panel</small>
    </div>

    <nav class="nav flex-column pb-4">
        @if ($user?->isAdmin())
            <p class="rk-sidebar__heading">Ringkasan</p>
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-gauge-high"></i> Dasbor
            </a>

            <p class="rk-sidebar__heading">Transaksi</p>
            <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                <i class="fas fa-receipt"></i> Daftar Pesanan
                @if ($pendingOrderCount ?? 0)
                    <span class="badge rounded-pill text-bg-warning">{{ $pendingOrderCount }}</span>
                @endif
            </a>

            <p class="rk-sidebar__heading">Katalog</p>
            <a class="nav-link {{ request()->routeIs('admin.items.*') ? 'active' : '' }}" href="{{ route('admin.items.index') }}">
                <i class="fas fa-burger"></i> Menu
            </a>
            <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                <i class="fas fa-tags"></i> Kategori
            </a>

            <p class="rk-sidebar__heading">Pengguna</p>
            <a class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}" href="{{ route('admin.employees.index') }}">
                <i class="fas fa-users"></i> Karyawan
            </a>
            <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">
                <i class="fas fa-user-shield"></i> Role
            </a>
        @endif

        @if ($user?->isCashier() || $user?->isAdmin())
            <p class="rk-sidebar__heading">Kasir</p>
            <a class="nav-link {{ request()->routeIs('cashier.*') ? 'active' : '' }}" href="{{ route('cashier.orders.index') }}">
                <i class="fas fa-cash-register"></i> Konfirmasi Pesanan
                @if ($pendingOrderCount ?? 0)
                    <span class="badge rounded-pill text-bg-warning">{{ $pendingOrderCount }}</span>
                @endif
            </a>
        @endif

        @if ($user?->isChef() || $user?->isAdmin())
            <p class="rk-sidebar__heading">Dapur</p>
            <a class="nav-link {{ request()->routeIs('chef.*') ? 'active' : '' }}" href="{{ route('chef.orders.index') }}">
                <i class="fas fa-fire-burner"></i> Papan Dapur
                @if ($kitchenOrderCount ?? 0)
                    <span class="badge rounded-pill text-bg-primary">{{ $kitchenOrderCount }}</span>
                @endif
            </a>
        @endif

        <p class="rk-sidebar__heading">Lainnya</p>
        <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
            <i class="fas fa-user-gear"></i> Profil Saya
        </a>
        <a class="nav-link" href="{{ route('menu') }}" target="_blank" rel="noopener">
            <i class="fas fa-up-right-from-square"></i> Lihat Halaman Menu
        </a>
    </nav>
</aside>

<div class="rk-backdrop"></div>
