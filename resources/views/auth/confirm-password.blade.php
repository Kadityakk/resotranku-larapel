@extends('auth.layouts.master')

@section('title', 'Konfirmasi Password')
@section('card_title', 'Konfirmasi password')
@section('card_subtitle', 'Ini area yang dilindungi. Masukkan password Anda untuk melanjutkan.')

@section('content')
<form method="POST" action="{{ route('password.confirm') }}" novalidate>
    @csrf

    <div class="mb-4">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" id="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required autocomplete="current-password" autofocus>
            <button type="button" class="rk-auth__toggle" data-password-toggle="#password"
                    aria-label="Tampilkan password">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2">Konfirmasi</button>
</form>
@endsection
