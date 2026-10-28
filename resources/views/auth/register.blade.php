@extends('auth.layouts.master')

@section('title', 'Daftar')
@section('card_title', 'Buat akun pelanggan')
@section('card_subtitle', 'Akun staf dibuat oleh admin melalui menu Karyawan.')

@section('content')
<form method="POST" action="{{ route('register') }}" novalidate>
    @csrf

    <div class="mb-3">
        <label for="fullname" class="form-label">Nama Lengkap</label>
        <input type="text" id="fullname" name="fullname" value="{{ old('fullname') }}" maxlength="255"
               class="form-control @error('fullname') is-invalid @enderror"
               required autofocus autocomplete="name">
        @error('fullname')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="row g-3">
        <div class="col-sm-6 mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" maxlength="50"
                   class="form-control @error('username') is-invalid @enderror"
                   required autocomplete="username">
            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-sm-6 mb-3">
            <label for="phone" class="form-label">Nomor WhatsApp</label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" maxlength="20"
                   class="form-control @error('phone') is-invalid @enderror"
                   required autocomplete="tel" placeholder="08xxxxxxxxxx">
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" maxlength="255"
               class="form-control @error('email') is-invalid @enderror"
               required autocomplete="email">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <input type="password" id="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required autocomplete="new-password">
            <button type="button" class="rk-auth__toggle" data-password-toggle="#password"
                    aria-label="Tampilkan password">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               class="form-control" required autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2">
        <i class="fas fa-user-plus me-1"></i> Daftar
    </button>

    <div class="rk-auth__divider">atau</div>

    <p class="text-center mb-0 small">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Masuk di sini</a>
    </p>
</form>
@endsection
