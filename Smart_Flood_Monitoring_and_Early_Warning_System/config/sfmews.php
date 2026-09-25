<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Alamat layanan yang dipanggil dari BROWSER
    |--------------------------------------------------------------------------
    | Dibiarkan kosong di produksi -> JavaScript memakai host halaman itu sendiri
    | (mis. http://103.127.133.95:8000), persis perilaku sebelumnya.
    |
    | Saat development lokal (php artisan serve), isi dengan alamat VPS supaya
    | halaman menampilkan data ASLI, bukan nilai contoh dari controller.
    */

    'api_public_url' => env('FASTAPI_PUBLIC_URL', ''),
    'ai_public_url' => env('AI_ENGINE_PUBLIC_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Alamat layanan yang dipanggil dari SISI SERVER (PHP)
    |--------------------------------------------------------------------------
    | Dipakai untuk login Admin (verifikasi ke MongoDB via FastAPI) dan panel
    | Kelola Sistem. Di dalam Docker bisa memakai nama service, mis.
    | http://fastapi-backend:8000
    */

    'api_url' => env('FASTAPI_URL', 'http://127.0.0.1:8000'),
    'ai_url' => env('AI_ENGINE_URL', 'http://127.0.0.1:5000'),

];
