<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

/**
 * Use case "Kelola Sistem" (khusus Admin).
 * Memberi kendali operasional yang sebelumnya hanya bisa lewat SSH/docker exec.
 */
class AdminController extends Controller
{
    private const TIMEOUT = 6;

    private function apiUrl(): string
    {
        return rtrim(config('sfmews.api_url'), '/');
    }

    private function aiUrl(): string
    {
        return rtrim(config('sfmews.ai_url'), '/');
    }

    /** Ambil JSON dengan aman: kembalikan null bila layanan mati/timeout. */
    private function ambil(string $url): ?array
    {
        try {
            $res = Http::timeout(self::TIMEOUT)->get($url);

            return $res->successful() ? $res->json() : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function system(): View
    {
        $health = $this->ambil($this->apiUrl() . '/health');
        $aiStatus = $this->ambil($this->aiUrl() . '/status');
        $worker = $this->ambil($this->aiUrl() . '/worker/status');
        $nodes = $this->ambil($this->apiUrl() . '/nodes/status');
        $flood = $this->ambil($this->apiUrl() . '/flood/status');

        return view('pages.admin.system', [
            'pageTitle' => 'Kelola Sistem',
            'pageSubtitle' => 'Panel Admin - SFMEWS',
            'stats' => [
                'active_cctv' => 3,
                'active_sensors' => collect($nodes['data'] ?? [])->where('online', true)->count(),
                'ai_status' => $aiStatus ? 'YOLOv8 Online' : 'YOLOv8 Offline',
                'system_status' => $health ? 'Online' : 'Offline',
            ],
            'apiOnline' => (bool) $health,
            'aiOnline' => (bool) $aiStatus,
            // null = endpoint kontrol worker belum tersedia di AI engine
            'workerEnabled' => $worker['enabled'] ?? null,
            'workerInterval' => $worker['interval_detik'] ?? null,
            'nodes' => $nodes['data'] ?? [],
            'floods' => $flood['data'] ?? [],
        ]);
    }

    /** Use case "Kelola Titik Monitoring" — daftar titik yang dapat disunting. */
    public function points(): View
    {
        $lokasi = $this->ambil($this->apiUrl() . '/locations');
        $nodes = $this->ambil($this->apiUrl() . '/nodes/status');
        $health = $this->ambil($this->apiUrl() . '/health');
        $aiStatus = $this->ambil($this->aiUrl() . '/status');

        return view('pages.admin.points', [
            'pageTitle' => 'Kelola Titik Monitoring',
            'pageSubtitle' => 'Panel Admin - SFMEWS',
            'stats' => [
                'active_cctv' => count($lokasi['data'] ?? []),
                'active_sensors' => collect($nodes['data'] ?? [])->where('online', true)->count(),
                'ai_status' => $aiStatus ? 'YOLOv8 Online' : 'YOLOv8 Offline',
                'system_status' => $health ? 'Online' : 'Offline',
            ],
            'lokasi' => $lokasi['data'] ?? [],
            'apiOnline' => (bool) $lokasi,
        ]);
    }

    /** Simpan perubahan satu titik monitoring. */
    public function updatePoint(Request $request, string $id): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'nama_pendek' => ['required', 'string', 'max:60'],
            'kecamatan' => ['nullable', 'string', 'max:120'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'kode_cctv' => ['nullable', 'string', 'max:60'],
            'cctv_live_url' => ['nullable', 'url', 'max:400'],
            'cctv_fallback_url' => ['nullable', 'string', 'max:400'],
        ]);

        try {
            $res = Http::timeout(self::TIMEOUT)
                ->acceptJson()
                ->put($this->apiUrl() . '/locations/' . urlencode($id), $data);

            if (! $res->successful() || ! $res->json('ok')) {
                return back()->with('gagal', $res->json('message') ?? 'Gagal menyimpan perubahan.');
            }

            return back()->with('sukses', 'Titik "' . $data['nama_pendek'] . '" berhasil diperbarui.');
        } catch (\Throwable $e) {
            return back()->with('gagal', 'Tidak dapat menghubungi API backend.');
        }
    }

    /** Nyalakan/matikan worker deteksi AI CCTV tanpa restart container. */
    public function toggleWorker(Request $request): RedirectResponse
    {
        $aktif = $request->boolean('enabled');

        try {
            $res = Http::timeout(self::TIMEOUT)
                ->asForm()
                ->post($this->aiUrl() . '/worker/toggle', ['enabled' => $aktif ? '1' : '0']);

            if (! $res->successful()) {
                return back()->with('gagal', 'AI Engine menolak permintaan (HTTP ' . $res->status() . ').');
            }

            $kini = $res->json('enabled');

            return back()->with('sukses', 'Worker deteksi AI berhasil ' . ($kini ? 'DINYALAKAN' : 'DIMATIKAN') . '.');
        } catch (\Throwable $e) {
            return back()->with('gagal', 'Tidak dapat menghubungi AI Engine. Pastikan layanan aktif.');
        }
    }
}
