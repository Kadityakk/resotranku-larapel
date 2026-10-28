@php
    $isEdit = isset($item);
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="rk-card p-4">
            <div class="mb-3">
                <label for="name" class="form-label">Nama Menu <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" maxlength="255" required
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $item->name ?? '') }}">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                <textarea id="description" name="description" rows="4" maxlength="1000" required
                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $item->description ?? '') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="price" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                    <input type="number" id="price" name="price" min="0" step="500" required
                           class="form-control @error('price') is-invalid @enderror"
                           value="{{ old('price', $item->price ?? '') }}">
                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select id="category_id" name="category_id" required
                            class="form-select @error('category_id') is-invalid @enderror">
                        <option value="">&mdash; Pilih kategori &mdash;</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                @selected((int) old('category_id', $item->category_id ?? 0) === $category->id)>
                                {{ $category->cat_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="rk-card p-4 mb-3">
            <label for="img" class="form-label">Gambar Menu</label>
            <img src="{{ $isEdit ? $item->imageUrl() : '' }}" id="img-preview"
                 class="rk-thumb w-100 mb-3 {{ $isEdit && $item->img ? '' : 'd-none' }}"
                 style="height: 180px;" alt="Pratinjau gambar">
            <input type="file" id="img" name="img" accept="image/jpeg,image/png,image/webp"
                   class="form-control @error('img') is-invalid @enderror"
                   data-image-input data-image-preview="#img-preview">
            @error('img')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <small class="text-body-secondary d-block mt-2">
                JPG, PNG atau WEBP, maksimal 2 MB.
                @if ($isEdit)
                    Biarkan kosong untuk mempertahankan gambar saat ini.
                @endif
            </small>
        </div>

        <div class="rk-card p-4">
            <div class="form-check form-switch">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" class="form-check-input" role="switch" id="is_active" name="is_active" value="1"
                       @checked((bool) old('is_active', $item->is_active ?? true))>
                <label class="form-check-label" for="is_active">Tampilkan di halaman pelanggan</label>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.items.index') }}" class="btn btn-light border">Batal</a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-floppy-disk me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Menu' }}
    </button>
</div>
