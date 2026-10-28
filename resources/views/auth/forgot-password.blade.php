@extends('auth.layouts.master')

@section('title', 'Lupa Password')
@section('card_title', 'Lupa password?')
@section('card_subtitle', 'Masukkan email Anda dan kami akan mengirimkan tautan untuk membuat password baru.')

@section('content')
<form method="POST" action="{{ route('password.email') }}" novalidate>
    @csrf

    <div class="mb-4">
        <label for="email" class="form-label">Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   required autofocus autocomplete="email">
        </div>
        @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2">
        <i class="fas fa-paper-plane me-1"></i> Kirim Tautan Reset
    </button>

    <p class="text-center mb-0 small mt-4">
        <a href="{{ route('login') }}" class="text-decoration-none">Kembali ke halaman masuk</a>
    </p>
</form>
@endsection
