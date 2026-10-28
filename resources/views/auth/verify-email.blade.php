@extends('auth.layouts.master')

@section('title', 'Verifikasi Email')
@section('card_title', 'Verifikasi email Anda')
@section('card_subtitle', 'Kami sudah mengirim tautan verifikasi ke email Anda. Klik tautan tersebut untuk melanjutkan.')

@section('content')
@if (session('status') === 'verification-link-sent')
    <div class="alert alert-success" role="alert">
        Tautan verifikasi baru sudah dikirim ke email Anda.
    </div>
@endif

<div class="d-grid gap-2">
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="fas fa-paper-plane me-1"></i> Kirim Ulang Email Verifikasi
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-light border w-100">Keluar</button>
    </form>
</div>
@endsection
