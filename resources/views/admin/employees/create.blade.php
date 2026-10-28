@extends('admin.layouts.master')

@section('title', 'Tambah Karyawan')
@section('heading', 'Tambah Karyawan')
@section('subheading', 'Buat akun staf baru dan tentukan rolenya')

@section('content')
<form action="{{ route('admin.employees.store') }}" method="POST">
    @csrf
    @include('admin.employees._form')
</form>
@endsection
