@extends('admin.layouts.master')

@section('title', 'Ubah Role')
@section('heading', 'Ubah Role')
@section('subheading', $role->role_name)

@section('content')
<form action="{{ route('admin.roles.update', $role) }}" method="POST">
    @csrf
    @method('PUT')
    @include('admin.roles._form')
</form>
@endsection
