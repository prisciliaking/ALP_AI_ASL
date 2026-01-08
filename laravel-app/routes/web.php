<?php
use App\Http\Controllers\Controller;
use App\Http\Controllers\ASLController;
use Illuminate\Support\Facades\Route;

// 1. Tambahkan ini untuk redirect dari root (/) ke /asl
Route::get('/', function () {
    return redirect('/asl');
});

// 2. Route utama kamu tetap ada
Route::get('/asl', [ASLController::class, 'index']);

// 3. Halaman Belajar (Kamera & Gambar) - Kita bawa parameter {letter}
Route::get('/asl/learn/{letter}', [ASLController::class, 'learn'])->name('asl.learn');

// 4. API Predict (Tetap sama)
Route::post('/predict', [ASLController::class, 'predict']);