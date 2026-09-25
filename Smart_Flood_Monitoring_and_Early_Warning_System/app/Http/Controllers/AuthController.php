<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Login/Logout Admin (use case "Login").
 *
 * Kredensial diverifikasi ke MongoDB LEWAT FastAPI — sama seperti seluruh data
 * lain (sensor, AI, riwayat). Laravel tidak pernah mengakses database langsung;
 * FastAPI adalah satu-satunya lapisan akses data. Sesi tetap dikelola Laravel.
 */
class AuthController extends Controller
{
    private const SESI = 'admin_user';

    private function apiUrl(): string
    {
        return rtrim(config('sfmews.api_url'), '/');
    }

    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->has(self::SESI)) {
            return redirect()->route('admin.system');
        }

        return view('pages.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $res = Http::timeout(8)->acceptJson()->post($this->apiUrl() . '/auth/login', [
                'email' => $data['email'],
                'password' => $data['password'],
            ]);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'email' => 'Tidak dapat menghubungi server autentikasi. Pastikan API backend aktif.',
            ]);
        }

        if ($res->status() === 401 || ! $res->successful() || ! $res->json('ok')) {
            throw ValidationException::withMessages([
                'email' => $res->json('message') ?? 'Email atau kata sandi tidak cocok.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put(self::SESI, $res->json('user'));

        return redirect()->intended(route('admin.system'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESI);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dashboard');
    }
}
