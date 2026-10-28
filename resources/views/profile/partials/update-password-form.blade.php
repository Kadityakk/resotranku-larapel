<section class="rk-card p-4 mb-4">
    <h2 class="h6 mb-1">Ubah Password</h2>
    <p class="text-body-secondary small">Gunakan password yang panjang dan unik agar akun tetap aman.</p>

    @if (session('status') === 'password-updated')
        <div class="alert alert-success py-2 small">Password berhasil diperbarui.</div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="current_password" class="form-label">Password Saat Ini</label>
            <input type="password" id="current_password" name="current_password" required
                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                   autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password" class="form-label">Password Baru</label>
            <input type="password" id="update_password" name="password" required
                   class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                   autocomplete="new-password">
            @error('password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" id="update_password_confirmation" name="password_confirmation" required
                   class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                   autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-key me-1"></i> Simpan Password
            </button>
        </div>
    </form>
</section>
