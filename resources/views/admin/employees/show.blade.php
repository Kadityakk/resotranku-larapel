@extends('admin.layouts.master')

@section('title', 'Detail Karyawan')
@section('heading', $employee->fullname)
@section('subheading', 'Detail akun karyawan')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="rk-card p-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <span class="rounded-circle text-bg-primary fw-bold fs-4"
                      style="width: 64px; height: 64px; display: grid; place-items: center;">
                    {{ str($employee->fullname)->substr(0, 1)->upper() }}
                </span>
                <div>
                    <h2 class="h5 mb-1">{{ $employee->fullname }}</h2>
                    <span class="badge rounded-pill text-bg-secondary">{{ $employee->role?->role_name ?? '-' }}</span>
                </div>
            </div>

            <dl class="row mb-0">
                <dt class="col-sm-4">Username</dt>
                <dd class="col-sm-8">{{ $employee->username }}</dd>

                <dt class="col-sm-4">Email</dt>
                <dd class="col-sm-8">{{ $employee->email }}</dd>

                <dt class="col-sm-4">Nomor Telepon</dt>
                <dd class="col-sm-8">{{ $employee->phone }}</dd>

                <dt class="col-sm-4">Role</dt>
                <dd class="col-sm-8">{{ $employee->role?->description ?? '-' }}</dd>

                <dt class="col-sm-4">Terdaftar</dt>
                <dd class="col-sm-8">{{ $employee->created_at?->translatedFormat('d F Y H:i') ?? '-' }}</dd>

                <dt class="col-sm-4">Pesanan Ditangani</dt>
                <dd class="col-sm-8">{{ $employee->orders()->count() }} pesanan</dd>
            </dl>

            <div class="d-flex gap-2 mt-4">
                <a href="{{ route('admin.employees.index') }}" class="btn btn-light border">Kembali</a>
                <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-primary">
                    <i class="fas fa-pen me-1"></i> Ubah Data
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
