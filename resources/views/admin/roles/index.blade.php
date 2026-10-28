@extends('admin.layouts.master')

@section('title', 'Role')
@section('heading', 'Role Pengguna')
@section('subheading', 'Role menentukan halaman dan tindakan yang dapat diakses setiap pengguna')

@section('content')
<div class="rk-card">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="h6 mb-0">{{ $roles->count() }} role terdaftar</h2>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Tambah Role
        </a>
    </div>

    <div class="alert alert-info rounded-0 border-0 mb-0 small">
        <i class="fas fa-circle-info me-1"></i>
        Role bawaan sistem (<strong>admin</strong>, <strong>cashier</strong>, <strong>chef</strong>, <strong>user</strong>)
        mengatur hak akses aplikasi sehingga namanya tidak dapat diubah atau dihapus.
    </div>

    <div class="table-responsive p-3">
        <table class="table table-hover align-middle w-100" data-datatable>
            <thead class="table-light">
                <tr>
                    <th>Nama Role</th>
                    <th>Deskripsi</th>
                    <th>Jumlah Pengguna</th>
                    <th>Jenis</th>
                    <th class="no-sort text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr>
                        <td class="fw-semibold">{{ $role->role_name }}</td>
                        <td>{{ $role->description }}</td>
                        <td>
                            <span class="badge rounded-pill text-bg-light">{{ $role->users_count }} pengguna</span>
                        </td>
                        <td>
                            @if ($role->isBuiltIn())
                                <span class="badge rounded-pill text-bg-secondary">
                                    <i class="fas fa-lock me-1"></i> Bawaan
                                </span>
                            @else
                                <span class="badge rounded-pill text-bg-primary">Kustom</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-light border" title="Ubah">
                                <i class="fas fa-pen"></i>
                            </a>
                            @if ($role->isBuiltIn())
                                <button type="button" class="btn btn-sm btn-light border text-muted" disabled
                                        title="Role bawaan tidak dapat dihapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @else
                                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline"
                                      data-confirm-delete
                                      data-confirm-title="Hapus role ini?"
                                      data-confirm-text="Role yang masih dipakai oleh pengguna tidak dapat dihapus.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border text-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
