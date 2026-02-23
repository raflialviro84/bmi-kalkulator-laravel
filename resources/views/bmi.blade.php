@extends('layouts.app')

@section('content')
<div style="position:fixed;top:24px;right:24px;z-index:1000;">
    <a href="/profile" style="text-decoration:none;">
        <div style="display:flex;align-items:center;background:#f3f4f6;border-radius:12px;padding:10px 18px;box-shadow:0 2px 8px #0001;min-width:120px;cursor:pointer;transition:box-shadow .2s;">
            <div style="width:36px;height:36px;border-radius:50%;background:#d1d5db;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:18px;color:#374151;margin-right:12px;">
                <span>
                    @php
                        $userName = session('user_name', 'Akun');
                        $initial = strtoupper(mb_substr($userName,0,1));
                    @endphp
                    {{ $initial }}
                </span>
            </div>
            <div style="color:#374151;font-size:15px;font-weight:500;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                {{ $userName }}
            </div>
        </div>
    </a>
</div>
<div class="container">
    <div class="box">
        <h1>BMI Kalkulator</h1>
        <div class="content">
            <form method="POST" action="{{ route('bmi.calculate') }}">
                @csrf
                <div class="input">
                    <label for="age">Umur (Tahun)</label>
                    <input type="text" name="age" id="age" value="{{ old('age', $input['age'] ?? '') }}" required />
                </div>
                <div class="gender">
                    <label class="container">
                        <input type="radio" name="gender" value="male" {{ old('gender', $input['gender'] ?? '') == 'male' ? 'checked' : '' }} />
                        <p class="text">Laki-Laki</p>
                        <span class="checkmark"></span>
                    </label>
                    <label class="container">
                        <input type="radio" name="gender" value="female" {{ old('gender', $input['gender'] ?? '') == 'female' ? 'checked' : '' }} />
                        <p class="text">Perempuan</p>
                        <span class="checkmark"></span>
                    </label>
                </div>
                <div class="containerHW">
                    <div class="inputH">
                        <label for="height">Tinggi (cm)</label>
                        <input type="number" name="height" id="height" value="{{ old('height', $input['height'] ?? '') }}" required />
                    </div>
                    <div class="inputW">
                        <label for="weight">Berat (kg)</label>
                        <input type="number" name="weight" id="weight" value="{{ old('weight', $input['weight'] ?? '') }}" required />
                    </div>
                </div>
                <button class="calculate" type="submit">Kalkulasi BMI</button>
            </form>
        </div>
        <div class="result">
            <p>BMI Anda adalah</p>
            <div id="result">{{ $bmi ?? '00.00' }}</div>
            @if(isset($result))
                <p class="comment">Anda <span id="comment">{{ $result }}</span></p>
            @else
                <p class="comment"></p>
            @endif
        </div>
        @if($errors->any())
            <div class="modal" style="display:block;">
                <div class="modal-content modal-wrong">
                    <span class="close" onclick="this.parentElement.parentElement.style.display='none';">&times;</span>
                    <p id="modalText">{{ $errors->first() }}</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
