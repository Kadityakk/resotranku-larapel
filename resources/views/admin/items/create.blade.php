@extends('admin.layouts.master')

@section('title', 'Tambah Menu')
@section('heading', 'Tambah Menu')
@section('subheading', 'Menu baru langsung tampil di halaman pelanggan bila diaktifkan')

@section('content')
<form action="{{ route('admin.items.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('admin.items._form')
</form>
@endsection
