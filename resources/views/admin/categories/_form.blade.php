@php
    $isEdit = isset($category);
@endphp

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="rk-card p-4">
            <div class="mb-3">
                <label for="cat_name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                <input type="text" id="cat_name" name="cat_name" maxlength="255" required
                       class="form-control @error('cat_name') is-invalid @enderror"
                       value="{{ old('cat_name', $category->cat_name ?? '') }}">
                @error('cat_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                <textarea id="description" name="description" rows="3" maxlength="500" required
                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description ?? '') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-light border">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-floppy-disk me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Kategori' }}
                </button>
            </div>
        </div>
    </div>
</div>
