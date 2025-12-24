<?php
use App\Http\Controllers\Controller;
use App\Http\Controllers\ASLController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
// Halaman utama deteksi
Route::get('/asl', [ASLController::class, 'asl_detect']);

// Endpoint untuk menerima data dari JavaScript
Route::post('/predict', [ASLController::class, 'predict']);