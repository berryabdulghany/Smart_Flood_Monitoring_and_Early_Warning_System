<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\RealtimeWeatherController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/api/weather/realtime', RealtimeWeatherController::class)->name('api.weather.realtime');

/*
|--------------------------------------------------------------------------
| AREA PUBLIK — aktor "User Monitoring" (tanpa login)
|--------------------------------------------------------------------------
| Sengaja terbuka: sistem peringatan dini harus dapat diakses masyarakat
| tanpa akun.
*/
Route::controller(DashboardController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    Route::get('/flood-map-gis', 'floodMap')->name('flood-map');
    Route::get('/cctv-monitoring', 'cctv')->name('cctv');
    Route::get('/iot-monitoring', 'iot')->name('iot');
    Route::get('/detection-history', 'history')->name('history');
});

/*
|--------------------------------------------------------------------------
| AUTENTIKASI — use case "Login" (hanya Admin)
|--------------------------------------------------------------------------
*/
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    // Batasi percobaan login: 5 kali per menit per IP (anti tebak kata sandi).
    Route::post('/login', 'login')->middleware('throttle:5,1');
    Route::post('/logout', 'logout')->name('logout');
});

/*
|--------------------------------------------------------------------------
| AREA ADMIN — wajib login («include» Login)
|--------------------------------------------------------------------------
*/
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/kelola-sistem', [AdminController::class, 'system'])->name('system');
    Route::post('/worker-ai', [AdminController::class, 'toggleWorker'])->name('worker.toggle');
    Route::get('/kelola-titik', [AdminController::class, 'points'])->name('points');
    Route::put('/kelola-titik/{id}', [AdminController::class, 'updatePoint'])->name('points.update');
});
