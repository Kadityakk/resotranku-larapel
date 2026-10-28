@extends('auth.layouts.master')

@section('title', 'Masuk')
@section('card_title', 'Selamat datang kembali')
@section('card_subtitle', 'Masuk dengan username atau email Anda untuk melanjutkan.')

@section('content')
<form method="POST" action="{{ route('login') }}" novalidate>
    @csrf

    <div class="mb-3">
        <label for="login" class="form-label">Username atau Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <input type="text" id="login" name="login" value="{{ old('login') }}"
                   class="form-control @error('login') is-invalid @enderror"
                   required autofocus autocomplete="username" placeholder="admin">
        </div>
        @error('login')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <label for="password" class="form-label">Password</label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="small text-decoration-none">Lupa password?</a>
            @endif
        </div>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" id="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required autocomplete="current-password" placeholder="Masukkan password">
            <button type="button" class="rk-auth__toggle" data-password-toggle="#password"
                    aria-label="Tampilkan password">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        @error('password')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-check mb-4">
        <input type="checkbox" class="form-check-input" id="remember" name="remember" @checked(old('remember'))>
        <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2">
        <i class="fas fa-arrow-right-to-bracket me-1"></i> Masuk
    </button>

    @if (Route::has('register'))
        <div class="rk-auth__divider">atau</div>

        <p class="text-center mb-0 small">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-decoration-none fw-semibold">Daftar sebagai pelanggan</a>
        </p>
    @endif
</form>
@endsection
