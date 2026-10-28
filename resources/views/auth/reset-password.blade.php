@extends('auth.layouts.master')

@section('title', 'Atur Password Baru')
@section('card_title', 'Atur password baru')
@section('card_subtitle', 'Buat password baru untuk akun Anda.')

@section('content')
<form method="POST" action="{{ route('password.store') }}" novalidate>
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $request->email) }}"
               class="form-control @error('email') is-invalid @enderror"
               required autofocus autocomplete="email">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password Baru</label>
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
               class="form-control @error('password_confirmation') is-invalid @enderror"
               required autocomplete="new-password">
        @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2">
        <i class="fas fa-key me-1"></i> Simpan Password Baru
    </button>
</form>
@endsection
