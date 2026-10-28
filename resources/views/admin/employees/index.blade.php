@extends('admin.layouts.master')

@section('title', 'Karyawan')
@section('heading', 'Data Karyawan')
@section('subheading', 'Kelola akun staf dan role yang mereka pegang')

@section('content')
<div class="rk-card">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="h6 mb-0">{{ $employees->count() }} pengguna terdaftar</h2>
        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-user-plus me-1"></i> Tambah Karyawan
        </a>
    </div>

    <div class="table-responsive p-3">
        <table class="table table-hover align-middle w-100" data-datatable>
            <thead class="table-light">
                <tr>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th>Kontak</th>
                    <th>Role</th>
                    <th>Terdaftar</th>
                    <th class="no-sort text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $employee)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-circle text-bg-primary fw-bold flex-shrink-0"
                                      style="width: 36px; height: 36px; display: grid; place-items: center;">
                                    {{ str($employee->fullname)->substr(0, 1)->upper() }}
                                </span>
                                <span class="fw-semibold">{{ $employee->fullname }}</span>
                                @if ($employee->is(auth()->user()))
                                    <span class="badge rounded-pill text-bg-light">Anda</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ $employee->username }}</td>
                        <td>
                            <span class="d-block small">{{ $employee->email }}</span>
                            <small class="text-body-secondary">{{ $employee->phone }}</small>
                        </td>
                        <td>
                            <span class="badge rounded-pill text-bg-secondary">{{ $employee->role?->role_name ?? '-' }}</span>
                        </td>
                        <td data-order="{{ $employee->created_at?->timestamp ?? 0 }}">
                            {{ $employee->created_at?->translatedFormat('d M Y') ?? '-' }}
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.employees.show', $employee) }}" class="btn btn-sm btn-light border" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-sm btn-light border" title="Ubah">
                                <i class="fas fa-pen"></i>
                            </a>
                            @if ($employee->is(auth()->user()))
                                <button type="button" class="btn btn-sm btn-light border text-muted" disabled
                                        title="Anda tidak dapat menghapus akun sendiri">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @else
                                <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" class="d-inline"
                                      data-confirm-delete
                                      data-confirm-title="Hapus karyawan ini?"
                                      data-confirm-text="Akun {{ $employee->fullname }} tidak akan bisa masuk lagi ke sistem.">
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
