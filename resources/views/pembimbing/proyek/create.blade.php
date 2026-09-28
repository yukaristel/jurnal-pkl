@extends('layouts.admin')
@section('title', 'Beri Proyek')
@section('page-title', 'Beri Proyek')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('pembimbing.proyek.store') }}">
            @csrf
            @include('pembimbing.proyek._form')
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('pembimbing.proyek.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection