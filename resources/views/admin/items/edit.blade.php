@extends('admin.layouts.master')

@section('title', 'Ubah Menu')
@section('heading', 'Ubah Menu')
@section('subheading', $item->name)

@section('content')
<form action="{{ route('admin.items.update', $item) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('admin.items._form')
</form>
@endsection
