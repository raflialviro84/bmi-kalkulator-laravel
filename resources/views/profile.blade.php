@extends('layouts.app')

@section('content')
<div class="container" style="max-width:500px;margin:auto;margin-top:60px;">
    <h2 class="mb-4">Profil Akun Fithub</h2>
    <div class="card p-4">
        <div class="mb-3">
            <strong>Nama:</strong> {{ $user['name'] ?? ($user->name ?? '-') }}
        </div>
        <div class="mb-3">
            <strong>Email:</strong> {{ $user['email'] ?? ($user->email ?? '-') }}
        </div>
            <div class="mb-3">
                <strong>Gender:</strong> {{ session('user_gender') ?? ($user['gender'] ?? ($user->gender ?? '-')) }}
            </div>
            <div class="mb-3">
                <strong>Birthdate:</strong> {{ session('user_birthdate') ?? ($user['birthdate'] ?? ($user->birthdate ?? '-')) }}
            </div>
            <div class="mb-3">
                <strong>Phone:</strong> {{ session('user_phone') ?? ($user['phone'] ?? ($user->phone ?? '-')) }}
            </div>
            <div class="mb-3">
                <strong>Role:</strong>
                @if(!empty($dbUser) && $dbUser->role)
                    {{ $dbUser->role->display_name ?? $dbUser->role->name }}
                @else
                    {{ session('user_role') ?? '-' }}
                @endif
            </div>
        <!-- Tambahkan field lain jika ada -->
    </div>
    <div class="mt-3" style="display:flex;gap:10px;">
        <a href="/" class="btn btn-secondary">Kembali ke Kalkulator</a>
        <form method="POST" action="{{ url('/logout') }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-danger">Logout</button>
        </form>
    </div>
</div>
@endsection
