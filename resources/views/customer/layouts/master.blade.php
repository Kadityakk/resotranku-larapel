@include('customer.layouts.__header')

    <body>

        <!-- Spinner Start -->
        <div id="spinner" class="show w-100 vh-100 position-fixed translate-middle top-50 start-50  d-flex align-items-center justify-content-center">
            <div class="spinner-grow text-primary" role="status"></div>
        </div>
        <!-- Spinner End -->

        <!-- Navbar start -->
        @include('customer.layouts.__navbar')
        <!-- Navbar End -->

        <!-- Toasts for cart feedback -->
        <div class="toast-stack" id="toast-stack"></div>

        <!-- Flash messages -->
        <div class="container">
            @foreach (['success' => 'success', 'error' => 'danger', 'info' => 'info'] as $key => $variant)
                @if (session($key))
                    <div class="alert alert-{{ $variant }} alert-dismissible fade show" role="alert">
                        {{ session($key) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif
            @endforeach

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Periksa kembali data Anda:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif
        </div>

        <!-- Main Start-->
        @yield('content')
        <!-- Main End-->

        <!-- Footer Start -->
        @include('customer.layouts.__footer')
        <!-- Footer End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-primary rounded-circle back-to-top" aria-label="Kembali ke atas"><i class="fa fa-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/customer/lib/easing/easing.min.js') }}"></script>
    <script src="{{ asset('assets/customer/lib/waypoints/waypoints.min.js') }}"></script>
    <script src="{{ asset('assets/customer/lib/lightbox/js/lightbox.min.js') }}"></script>
    <script src="{{ asset('assets/customer/lib/owlcarousel/owl.carousel.min.js') }}"></script>

    <!-- Template Javascript -->
    <script src="{{ asset('assets/customer/js/main.js') }}"></script>
    <script src="{{ asset('assets/customer/js/cart.js') }}"></script>

    @yield('scripts')
    @stack('scripts')
    </body>
</html>
