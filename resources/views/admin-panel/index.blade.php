<?php /** roles index view */ ?>
@extends('layouts.app')

@section('content')
<div class="container" style="max-width:900px;margin:auto;margin-top:40px;">
    <h2 class="mb-4">Manajemen Role</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('roles.create') }}" class="btn btn-primary">Buat Role Baru</a>
        <a href="/" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="card p-3">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Display</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td>{{ $r->name }}</td>
                    <td>{{ $r->display_name }}</td>
                    <td style="display:flex;gap:8px;">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('roles.edit', $r) }}">Edit</a>
                        <form method="POST" action="{{ route('roles.destroy', $r) }}" onsubmit="return confirm('Hapus role ini?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
