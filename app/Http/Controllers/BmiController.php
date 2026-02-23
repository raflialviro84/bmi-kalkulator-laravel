<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BmiController extends Controller
{
    public function index()
    {
        return view('bmi');
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'age' => 'required|integer|min:1',
            'gender' => 'required|in:male,female',
            'height' => 'required|numeric|min:1',
            'weight' => 'required|numeric|min:1',
        ]);

        $bmi = $validated['weight'] / pow($validated['height'] / 100, 2);
        $result = '';
        if ($bmi < 18.5) {
            $result = 'Kekurangan Berat Badan';
        } elseif ($bmi <= 24.9) {
            $result = 'Normal (Ideal)';
        } elseif ($bmi <= 29.9) {
            $result = 'Kelebihan Berat Badan';
        } elseif ($bmi <= 34.9) {
            $result = 'Kegemukan (Obesitas)';
        } else {
            $result = 'Obesitas Ekstrem';
        }

        return view('bmi', [
            'bmi' => number_format($bmi, 2),
            'result' => $result,
            'input' => $validated
        ]);
    }
}
