<section class="rk-card p-4">
    <h2 class="h6 mb-1">Informasi Akun</h2>
    <p class="text-body-secondary small">Perbarui nama, username dan kontak akun Anda.</p>

    @if (session('status') === 'profile-updated')
        <div class="alert alert-success py-2 small">Profil berhasil diperbarui.</div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <label for="fullname" class="form-label">Nama Lengkap</label>
            <input type="text" id="fullname" name="fullname" maxlength="255" required
                   class="form-control @error('fullname') is-invalid @enderror"
                   value="{{ old('fullname', $user->fullname) }}" autocomplete="name">
            @error('fullname')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3">
            <div class="col-sm-6 mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" maxlength="50" required
                       class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username', $user->username) }}" autocomplete="username">
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6 mb-3">
                <label for="phone" class="form-label">Nomor Telepon</label>
                <input type="tel" id="phone" name="phone" maxlength="20" required
                       class="form-control @error('phone') is-invalid @enderror"
                       value="{{ old('phone', $user->phone) }}" autocomplete="tel">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" maxlength="255" required
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $user->email) }}" autocomplete="email">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Role</label>
            <input type="text" class="form-control" value="{{ $user->role?->role_name ?? '-' }}" disabled>
            <small class="text-body-secondary">Role hanya dapat diubah oleh admin.</small>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-floppy-disk me-1"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</section>
