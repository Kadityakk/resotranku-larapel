@include('admin.layouts.__head')

<body class="rk-admin">

    @include('admin.layouts.__sidebar')

    <div class="rk-content">
        @include('admin.layouts.__topbar')

        <main class="rk-main">
            @include('admin.partials.__alerts')

            @yield('content')
        </main>

        <footer class="rk-footer d-flex justify-content-between flex-wrap gap-2">
            <span>&copy; {{ now()->year }} Restoranku. Semua hak dilindungi.</span>
            <span>Masuk sebagai <strong>{{ auth()->user()?->username }}</strong></span>
        </footer>
    </div>

    @include('admin.layouts.__scripts')
</body>
</html>
