@extends('admin.layouts.master')

@section('title', 'Menu')
@section('heading', 'Daftar Menu')
@section('subheading', 'Kelola menu yang tampil di halaman pelanggan')

@section('content')
<div class="rk-card">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="h6 mb-0">{{ $items->count() }} menu terdaftar</h2>
        <a href="{{ route('admin.items.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Tambah Menu
        </a>
    </div>

    <div class="table-responsive p-3">
        <table class="table table-hover align-middle w-100" data-datatable data-order="[[5, &quot;desc&quot;]]">
            <thead class="table-light">
                <tr>
                    <th class="no-sort">Gambar</th>
                    <th>Nama Menu</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th class="no-sort text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>
                            <img src="{{ $item->imageUrl() }}" class="rk-thumb" alt="{{ $item->name }}" loading="lazy">
                        </td>
                        <td>
                            <span class="fw-semibold d-block">{{ $item->name }}</span>
                            <small class="text-body-secondary">{{ str($item->description)->limit(60) }}</small>
                        </td>
                        <td>{{ $item->category?->cat_name ?? '—' }}</td>
                        <td data-order="{{ $item->price }}"><x-rupiah :value="$item->price" /></td>
                        <td data-order="{{ (int) $item->is_active }}">
                            <form action="{{ route('admin.items.toggle', $item) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="btn btn-sm rounded-pill {{ $item->is_active ? 'btn-outline-primary' : 'btn-outline-secondary' }}"
                                        title="Klik untuk mengubah ketersediaan">
                                    <i class="fas {{ $item->is_active ? 'fa-circle-check' : 'fa-circle-xmark' }} me-1"></i>
                                    {{ $item->is_active ? 'Tersedia' : 'Tidak tersedia' }}
                                </button>
                            </form>
                        </td>
                        <td data-order="{{ $item->created_at?->timestamp ?? 0 }}">
                            {{ $item->created_at?->translatedFormat('d M Y') ?? '-' }}
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.items.show', $item) }}" class="btn btn-sm btn-light border" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.items.edit', $item) }}" class="btn btn-sm btn-light border" title="Ubah">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form action="{{ route('admin.items.destroy', $item) }}" method="POST" class="d-inline"
                                  data-confirm-delete
                                  data-confirm-title="Hapus menu &quot;{{ $item->name }}&quot;?"
                                  data-confirm-text="Menu yang sudah pernah dipesan akan diarsipkan agar riwayat pesanan tetap utuh.">
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
