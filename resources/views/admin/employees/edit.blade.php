@extends('admin.layouts.master')

@section('title', 'Ubah Karyawan')
@section('heading', 'Ubah Karyawan')
@section('subheading', $employee->fullname)

@section('content')
<form action="{{ route('admin.employees.update', $employee) }}" method="POST">
    @csrf
    @method('PUT')
    @include('admin.employees._form')
</form>
@endsection
