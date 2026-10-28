@extends('admin.layouts.master')

@section('title', 'Kategori')
@section('heading', 'Kategori Menu')
@section('subheading', 'Kelompokkan menu agar mudah ditemukan pelanggan')

@section('content')
<div class="rk-card">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="h6 mb-0">{{ $categories->count() }} kategori terdaftar</h2>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Tambah Kategori
        </a>
    </div>

    <div class="table-responsive p-3">
        <table class="table table-hover align-middle w-100" data-datatable>
            <thead class="table-light">
                <tr>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Jumlah Menu</th>
                    <th>Dibuat</th>
                    <th class="no-sort text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td class="fw-semibold">{{ $category->cat_name }}</td>
                        <td>{{ $category->description }}</td>
                        <td>
                            <span class="badge rounded-pill text-bg-light">{{ $category->items_count }} menu</span>
                        </td>
                        <td data-order="{{ $category->created_at?->timestamp ?? 0 }}">
                            {{ $category->created_at?->translatedFormat('d M Y') ?? '-' }}
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-light border" title="Ubah">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline"
                                  data-confirm-delete
                                  data-confirm-title="Hapus kategori ini?"
                                  data-confirm-text="Kategori yang masih dipakai oleh menu tidak dapat dihapus.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light border text-danger" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
