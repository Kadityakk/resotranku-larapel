@php
    $user = auth()->user();
@endphp

<header class="rk-topbar d-flex align-items-center gap-3">
    <button type="button" class="btn btn-sm btn-outline-secondary d-lg-none" data-sidebar-toggle aria-label="Buka menu">
        <i class="fas fa-bars"></i>
    </button>

    <div class="flex-grow-1 min-w-0">
        <h1 class="h5 mb-0 text-truncate">@yield('heading', 'Dasbor')</h1>
        @hasSection('subheading')
            <small class="text-body-secondary">@yield('subheading')</small>
        @endif
    </div>

    <div class="dropdown">
        <button class="btn btn-light border d-flex align-items-center gap-2 dropdown-toggle" type="button"
                data-bs-toggle="dropdown" aria-expanded="false">
            <span class="rounded-circle text-bg-primary d-grid place-items-center fw-bold"
                  style="width: 32px; height: 32px; display: grid; place-items: center;">
                {{ str($user?->fullname ?? 'S')->substr(0, 1)->upper() }}
            </span>
            <span class="d-none d-sm-inline text-start lh-sm">
                <span class="d-block small fw-semibold">{{ $user?->fullname }}</span>
                <span class="d-block text-body-secondary" style="font-size: .75rem;">{{ $user?->role?->role_name }}</span>
            </span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li>
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                    <i class="fas fa-user-gear me-2"></i> Profil Saya
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-arrow-right-from-bracket me-2"></i> Keluar
                    </button>
                </form>
            </li>
        </ul>
    </div>
</header>
