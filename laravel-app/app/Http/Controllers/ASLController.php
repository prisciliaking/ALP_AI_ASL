<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

// Harus extends Controller
class ASLController extends Controller
{
    public function index()
    {
        return view('asl_detect');
    }

    public function learn($letter)
    {
        // Kita kirim variabel $letter ke halaman learning_page
        return view('learning_page', ['letter' => strtoupper($letter)]);
    }

    public function predict(Request $request)
    {
        $landmarks = $request->input('landmarks');

        // Pastikan FastAPI (Python) kamu sudah jalan di port 8000
        $response = Http::post('http://127.0.0.1:8000/predict', [
            'landmarks' => $landmarks
        ]);

        return response()->json($response->json());
    }
}
