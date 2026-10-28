@extends('admin.layouts.master')

@section('title', 'Tambah Role')
@section('heading', 'Tambah Role')

@section('content')
<form action="{{ route('admin.roles.store') }}" method="POST">
    @csrf
    @include('admin.roles._form')
</form>
@endsection
