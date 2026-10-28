@extends('admin.layouts.master')

@section('title', 'Tambah Kategori')
@section('heading', 'Tambah Kategori')

@section('content')
<form action="{{ route('admin.categories.store') }}" method="POST">
    @csrf
    @include('admin.categories._form')
</form>
@endsection
