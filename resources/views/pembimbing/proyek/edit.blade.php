@extends('layouts.admin')
@section('title', 'Edit Proyek')
@section('page-title', 'Edit Proyek')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('pembimbing.proyek.update', $proyek) }}">
            @csrf
            @method('PUT')
            @include('pembimbing.proyek._form', ['proyek' => $proyek])
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('pembimbing.proyek.show', $proyek) }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection