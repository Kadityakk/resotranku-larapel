@extends('admin.layouts.master')

@section('title', 'Ubah Kategori')
@section('heading', 'Ubah Kategori')
@section('subheading', $category->cat_name)

@section('content')
<form action="{{ route('admin.categories.update', $category) }}" method="POST">
    @csrf
    @method('PUT')
    @include('admin.categories._form')
</form>
@endsection
