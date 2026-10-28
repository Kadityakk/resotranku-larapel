@extends('admin.layouts.master')

@section('title', 'Detail Menu')
@section('heading', $item->name)
@section('subheading', 'Detail menu')

@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        <div class="rk-card p-3">
            <img src="{{ $item->imageUrl() }}" class="w-100 rounded" style="height: 280px; object-fit: cover;"
                 alt="{{ $item->name }}">
        </div>
    </div>
    <div class="col-lg-7">
        <div class="rk-card p-4">
            <dl class="row mb-0">
                <dt class="col-sm-4">Nama</dt>
                <dd class="col-sm-8">{{ $item->name }}</dd>

                <dt class="col-sm-4">Kategori</dt>
                <dd class="col-sm-8">{{ $item->category?->cat_name ?? '-' }}</dd>

                <dt class="col-sm-4">Harga</dt>
                <dd class="col-sm-8"><x-rupiah :value="$item->price" /></dd>

                <dt class="col-sm-4">Status</dt>
                <dd class="col-sm-8">
                    <span class="badge rounded-pill text-bg-{{ $item->is_active ? 'primary' : 'secondary' }}">
                        {{ $item->is_active ? 'Tersedia' : 'Tidak tersedia' }}
                    </span>
                </dd>

                <dt class="col-sm-4">Deskripsi</dt>
                <dd class="col-sm-8">{{ $item->description }}</dd>

                <dt class="col-sm-4">Dibuat</dt>
                <dd class="col-sm-8">{{ $item->created_at?->translatedFormat('d F Y H:i') ?? '-' }}</dd>

                <dt class="col-sm-4">Diperbarui</dt>
                <dd class="col-sm-8">{{ $item->updated_at?->translatedFormat('d F Y H:i') ?? '-' }}</dd>
            </dl>

            <div class="d-flex gap-2 mt-4">
                <a href="{{ route('admin.items.index') }}" class="btn btn-light border">Kembali</a>
                <a href="{{ route('admin.items.edit', $item) }}" class="btn btn-primary">
                    <i class="fas fa-pen me-1"></i> Ubah Menu
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
