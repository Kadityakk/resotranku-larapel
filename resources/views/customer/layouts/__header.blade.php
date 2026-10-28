<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="utf-8">
        <title>@yield('title', 'Menu') &middot; Restoranku</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta content="restoran, menu, pesan online, qris" name="keywords">
        <meta content="Pesan menu favorit Anda langsung dari meja. Pilihan lezat di ujung jari Anda." name="description">

        <!-- Google Web Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@600;800&display=swap" rel="stylesheet">

        <!-- Icon Font Stylesheet -->
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

        <!-- Libraries Stylesheet -->
        <link href="{{ asset('assets/customer/lib/animate/animate.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/customer/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">

        <!-- Customized Bootstrap Stylesheet -->
        <link href="{{ asset('assets/customer/css/bootstrap.min.css') }}" rel="stylesheet">

        <!-- Template Stylesheet -->
        <link href="{{ asset('assets/customer/css/style.css') }}" rel="stylesheet">

        <style>
            .text-limited {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .cart-badge {
                position: absolute;
                top: -8px;
                right: -12px;
                min-width: 20px;
                font-size: .7rem;
            }

            .fruite-img {
                height: 220px;
                overflow: hidden;
            }

            .fruite-img img {
                height: 100%;
                object-fit: cover;
            }

            .toast-stack {
                position: fixed;
                top: 90px;
                right: 20px;
                z-index: 2000;
            }
        </style>

        @stack('styles')
    </head>
