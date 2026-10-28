<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk') &middot; Restoranku</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Raleway:wght@600;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('assets/admin/css/admin.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/auth/css/auth.css') }}" rel="stylesheet">
</head>

<body class="rk-auth">
    <div class="rk-auth__shell">

        <aside class="rk-auth__aside d-none d-lg-flex">
            <div class="rk-auth__aside-content">
                <a href="{{ route('menu') }}" class="rk-auth__brand text-decoration-none">
                    <h1>Restoranku</h1>
                    <p>Pilihan Lezat di Ujung Jari Anda!</p>
                </a>

                <ul class="rk-auth__features list-unstyled">
                    <li><i class="fas fa-qrcode"></i> Pesan langsung dari meja lewat QR code</li>
                    <li><i class="fas fa-cash-register"></i> Konfirmasi pembayaran tunai &amp; QRIS</li>
                    <li><i class="fas fa-fire-burner"></i> Papan dapur real-time untuk tim koki</li>
                    <li><i class="fas fa-chart-line"></i> Laporan penjualan harian untuk pemilik</li>
                </ul>

                <p class="rk-auth__aside-foot mb-0">
                    &copy; {{ now()->year }} Restoranku. Semua hak dilindungi.
                </p>
            </div>
        </aside>

        <main class="rk-auth__main">
            <div class="rk-auth__card">
                <a href="{{ route('menu') }}" class="rk-auth__brand rk-auth__brand--compact d-lg-none text-decoration-none">
                    <h1>Restoranku</h1>
                </a>

                <header class="mb-4">
                    <h2 class="h3 mb-1">@yield('card_title', 'Masuk')</h2>
                    <p class="text-body-secondary mb-0">@yield('card_subtitle')</p>
                </header>

                @if (session('status'))
                    <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                @endif

                @yield('content')
            </div>

            <p class="text-center text-body-secondary small mt-4 mb-0">
                <a href="{{ route('menu') }}" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke halaman menu
                </a>
            </p>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/auth/js/auth.js') }}"></script>
</body>
</html>
