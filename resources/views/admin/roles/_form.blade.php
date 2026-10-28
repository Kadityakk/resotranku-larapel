@php
    $isEdit = isset($role);
    $isBuiltIn = $isEdit && $role->isBuiltIn();
@endphp

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="rk-card p-4">
            @if ($isBuiltIn)
                <div class="alert alert-warning">
                    <i class="fas fa-lock me-1"></i>
                    Ini role bawaan sistem. Nama role tidak dapat diubah, tetapi deskripsinya bisa.
                </div>
            @endif

            <div class="mb-3">
                <label for="role_name" class="form-label">Nama Role <span class="text-danger">*</span></label>
                <input type="text" id="role_name" name="role_name" maxlength="255" required
                       class="form-control @error('role_name') is-invalid @enderror"
                       value="{{ old('role_name', $role->role_name ?? '') }}"
                       @readonly($isBuiltIn)>
                @error('role_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                <textarea id="description" name="description" rows="3" maxlength="500" required
                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $role->description ?? '') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-light border">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-floppy-disk me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Role' }}
                </button>
            </div>
        </div>
    </div>
</div>
