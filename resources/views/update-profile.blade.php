@extends('layouts.app')

@section('content')
<div class="container" style="max-width:600px;margin:auto;margin-top:60px;">
    <h2 class="mb-4">Perbarui Profil</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        <div class="card p-4">
            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $dbUser->name ?? ($user['name'] ?? '')) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Email (tidak dapat diubah)</label>
                <input type="email" class="form-control" readonly value="{{ $dbUser->email ?? ($user['email'] ?? '') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-control">
                    @php $curGender = old('gender', $dbUser->gender ?? session('user_gender')); $curGenderNorm = strtolower($curGender ?? ''); @endphp
                        <option value="">-- Pilih --</option>
                        <option value="male" {{ $curGenderNorm === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ $curGenderNorm === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ $curGenderNorm === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Birthdate</label>
                <input type="date" name="birthdate" class="form-control" value="{{ old('birthdate', isset($dbUser->birthdate) ? 
                    (strlen($dbUser->birthdate) ? 
                        
                        date('Y-m-d', strtotime($dbUser->birthdate)) : '') : (session('user_birthdate') ?? '')) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">No. Telepon</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $dbUser->phone ?? session('user_phone')) }}">
            </div>

            <div style="display:flex;gap:10px;margin-top:10px;">
                <a href="{{ route('profile') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </div>
    </form>
</div>
@endsection
