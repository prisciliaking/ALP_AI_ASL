<?php
use App\Http\Controllers\Controller;
use App\Http\Controllers\ASLController;
use Illuminate\Support\Facades\Route;

// 1. Halaman Pilih Huruf (Menu Utama)
Route::get('/asl', [ASLController::class, 'index']);

// 2. Halaman Belajar (Kamera & Gambar) - Kita bawa parameter {letter}
Route::get('/asl/learn/{letter}', [ASLController::class, 'learn'])->name('asl.learn');

// 3. API Predict (Tetap sama)
Route::post('/predict', [ASLController::class, 'predict']);