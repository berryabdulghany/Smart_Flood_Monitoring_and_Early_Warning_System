<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\RealtimeWeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/api/weather/realtime', RealtimeWeatherController::class)->name('api.weather.realtime');

Route::controller(DashboardController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    Route::get('/flood-map-gis', 'floodMap')->name('flood-map');
    Route::get('/cctv-monitoring', 'cctv')->name('cctv');
    Route::get('/iot-monitoring', 'iot')->name('iot');
    Route::get('/weather-monitoring', 'weather')->name('weather');
    Route::get('/detection-history', 'history')->name('history');
    Route::get('/settings', 'settings')->name('settings');
});
