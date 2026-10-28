@php
    $isEdit = isset($employee);
@endphp

<div class="row g-4">
    <div class="col-lg-7">
        <div class="rk-card p-4">
            <h2 class="h6 mb-3">Data Pribadi</h2>

            <div class="mb-3">
                <label for="fullname" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" id="fullname" name="fullname" maxlength="255" required
                       class="form-control @error('fullname') is-invalid @enderror"
                       value="{{ old('fullname', $employee->fullname ?? '') }}">
                @error('fullname')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" maxlength="255" required
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $employee->email ?? '') }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                    <input type="tel" id="phone" name="phone" maxlength="20" required
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $employee->phone ?? '') }}" placeholder="08xxxxxxxxxx">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="rk-card p-4 mb-3">
            <h2 class="h6 mb-3">Akun &amp; Akses</h2>

            <div class="mb-3">
                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" id="username" name="username" maxlength="50" required
                       class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username', $employee->username ?? '') }}">
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-body-secondary">Dipakai untuk masuk ke sistem.</small>
            </div>

            <div class="mb-0">
                <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                <select id="role_id" name="role_id" required
                        class="form-select @error('role_id') is-invalid @enderror">
                    <option value="">&mdash; Pilih role &mdash;</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}"
                            @selected((int) old('role_id', $employee->role_id ?? 0) === $role->id)>
                            {{ $role->role_name }} &mdash; {{ $role->description }}
                        </option>
                    @endforeach
                </select>
                @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="rk-card p-4">
            <h2 class="h6 mb-3">Password</h2>

            @if ($isEdit)
                <p class="text-body-secondary small">Biarkan kosong jika tidak ingin mengubah password.</p>
            @endif

            <div class="mb-3">
                <label for="password" class="form-label">
                    Password @unless ($isEdit)<span class="text-danger">*</span>@endunless
                </label>
                <input type="password" id="password" name="password" autocomplete="new-password"
                       class="form-control @error('password') is-invalid @enderror"
                       @required(! $isEdit)>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-0">
                <label for="password_confirmation" class="form-label">
                    Konfirmasi Password @unless ($isEdit)<span class="text-danger">*</span>@endunless
                </label>
                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                       class="form-control" @required(! $isEdit)>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.employees.index') }}" class="btn btn-light border">Batal</a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-floppy-disk me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Karyawan' }}
    </button>
</div>
