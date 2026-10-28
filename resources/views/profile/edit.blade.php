@extends('admin.layouts.master')

@section('title', 'Profil Saya')
@section('heading', 'Profil Saya')
@section('subheading', 'Perbarui data akun dan password Anda')

@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        @include('profile.partials.update-profile-information-form')
    </div>
    <div class="col-lg-5">
        @include('profile.partials.update-password-form')
        @include('profile.partials.delete-user-form')
    </div>
</div>
@endsection
