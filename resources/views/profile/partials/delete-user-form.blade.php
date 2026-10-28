<section class="rk-card p-4 border-danger-subtle">
    <h2 class="h6 mb-1 text-danger">Hapus Akun</h2>
    <p class="text-body-secondary small">
        Akun Anda akan dinonaktifkan dan Anda tidak dapat masuk lagi. Hubungi admin bila perlu dipulihkan.
    </p>

    <form method="POST" action="{{ route('profile.destroy') }}"
          data-confirm-delete
          data-confirm-title="Hapus akun Anda?"
          data-confirm-text="Anda akan langsung keluar dan tidak dapat masuk kembali dengan akun ini.">
        @csrf
        @method('DELETE')

        <div class="mb-3">
            <label for="delete_password" class="form-label">Konfirmasi dengan password Anda</label>
            <input type="password" id="delete_password" name="password" required
                   class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                   autocomplete="current-password">
            @error('password', 'userDeletion')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i> Hapus Akun
            </button>
        </div>
    </form>
</section>
