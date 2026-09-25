<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function dashboard(): View
    {
        return view('pages.dashboard', $this->dashboardData([
            'pageTitle' => 'Dashboard',
            'pageSubtitle' => 'Integrated AI, IoT, CCTV, GIS, and weather monitoring for Bandung City.',
        ]));
    }

    public function floodMap(): View
    {
        return view('pages.monitoring.flood-map', $this->dashboardData([
            'pageTitle' => 'Flood Map GIS',
            'pageSubtitle' => 'Fullscreen spatial monitoring with realtime flood markers and smart city overlays.',
        ]));
    }

    public function cctv(): View
    {
        return view('pages.monitoring.cctv', $this->dashboardData([
            'pageTitle' => 'CCTV Monitoring',
            'pageSubtitle' => 'Realtime CCTV preview with YOLOv8 flood detection status.',
        ]));
    }

    public function iot(): View
    {
        return view('pages.monitoring.iot', $this->dashboardData([
            'pageTitle' => 'IoT Monitoring',
            'pageSubtitle' => 'Water level, rain gauge, and DHT22 sensor telemetry dashboard.',
        ]));
    }

    public function history(): View
    {
        return view('pages.monitoring.history', $this->dashboardData([
            'pageTitle' => 'Flood Event History',
            'pageSubtitle' => 'Log keputusan banjir dari 3 indikator: level air, curah hujan, dan AI.',
        ]));
    }

    /**
     * Nilai bawaan bila API tidak dapat dihubungi, agar halaman tetap tampil.
     * Dipakai juga sebagai kerangka field runtime (status, level air, dst).
     */
    private const LOKASI_CADANGAN = [
        [
            'id' => 'kopo', 'name' => 'KOPO - RS Bandung Kiwari 02', 'short_name' => 'Kopo',
            'district' => 'RS Bandung Kiwari 02', 'lat' => -6.943258140459321, 'lng' => 107.59159188077126,
            'cctv' => 'BDG-KPO-01',
            'cctv_live_url' => 'https://pelindung.bandung.go.id:3443/video/DPU/kopocitarip.m3u8',
            'cctv_fallback_url' => '/videos/kopo-flood-transition.mp4',
        ],
        [
            'id' => 'pasir-koja', 'name' => 'SP Pasir Koja', 'short_name' => 'Pasir Koja',
            'district' => 'Bojongloa Kaler', 'lat' => -6.930461, 'lng' => 107.575984,
            'cctv' => 'BDG-PSK-03',
            'cctv_live_url' => 'https://pelindung.bandung.go.id:3443/video/DISHUB/sppasirkoja.m3u8',
            'cctv_fallback_url' => '/videos/pasirkoja-flood-transition.mp4',
        ],
        [
            'id' => 'gede-bage', 'name' => 'Gedebage Selatan - Jl. Derwati', 'short_name' => 'Gede Bage',
            'district' => 'Jl. Derwati', 'lat' => -6.965528, 'lng' => 107.686923,
            'cctv' => 'BDG-GDB-02',
            'cctv_live_url' => 'https://pelindung.bandung.go.id:3443/video/DPU/gdbageselatan.m3u8',
            'cctv_fallback_url' => '/videos/gedebage-flood-transition.mp4',
        ],
    ];

    /**
     * Konfigurasi titik monitoring dari MongoDB (via FastAPI) — dapat dikelola
     * Admin. Nilai runtime (status, level air, cuaca) sengaja dibiarkan netral
     * karena akan diisi realtime oleh JavaScript dari /flood/status; memakai
     * angka contoh di sini pernah membuat halaman menampilkan data palsu saat
     * API belum sempat terbaca.
     */
    private function konfigurasiLokasi(): array
    {
        $dariApi = [];

        try {
            $res = Http::timeout(5)->get(rtrim(config('sfmews.api_url'), '/') . '/locations');
            if ($res->successful()) {
                $dariApi = collect($res->json('data') ?? [])->keyBy('id')->all();
            }
        } catch (\Throwable $e) {
            // biarkan kosong -> pakai nilai cadangan
        }

        return collect(self::LOKASI_CADANGAN)->map(function (array $bawaan) use ($dariApi) {
            $api = $dariApi[$bawaan['id']] ?? [];

            return array_merge($bawaan, [
                'name' => $api['nama'] ?? $bawaan['name'],
                'short_name' => $api['nama_pendek'] ?? $bawaan['short_name'],
                'district' => $api['kecamatan'] ?? $bawaan['district'],
                'lat' => $api['lat'] ?? $bawaan['lat'],
                'lng' => $api['lng'] ?? $bawaan['lng'],
                'cctv' => $api['kode_cctv'] ?? $bawaan['cctv'],
                'cctv_live_url' => $api['cctv_live_url'] ?? $bawaan['cctv_live_url'],
                'cctv_fallback_url' => $api['cctv_fallback_url'] ?? $bawaan['cctv_fallback_url'],
                // ---- nilai runtime (netral, diisi realtime oleh JavaScript) ----
                'status' => 'safe',
                'status_label' => 'Aman',
                'status_color' => '#22c55e',
                'online' => true,
                'ai_confidence' => 0,
                'water_level' => 0,
                'rainfall' => 0,
                'temperature' => 0,
                'humidity' => 0,
                'weather' => '-',
                'rain_potential' => '-',
                'flood_prediction' => '-',
                'last_detection' => '-',
                'wind_speed' => 0,
                'battery' => 100,
            ]);
        })->values()->all();
    }

    private function dashboardData(array $page = []): array
    {
        $locations = $this->konfigurasiLokasi();

        return array_merge([
            'locations' => $locations,
            'stats' => [
                'active_cctv' => 3,
                'active_sensors' => 1,
                'ai_status' => 'YOLOv8 Online',
                'system_status' => 'Online',
            ],
            'weatherSummary' => [
                'temperature' => 25.7,
                'humidity' => 86,
                'rain_intensity' => 41,
                'wind_speed' => 12,
                'prediction' => 'Potensi banjir tinggi di Pasir Koja dalam 45-60 menit',
                'updated_at' => '01:15 WIB',
            ],
            'alerts' => [
                ['level' => 'danger', 'title' => 'Potensi banjir tinggi', 'location' => 'Pasir Koja', 'time' => '2 menit lalu'],
                ['level' => 'warning', 'title' => 'Water level meningkat', 'location' => 'Kopo', 'time' => '6 menit lalu'],
                ['level' => 'warning', 'title' => 'Curah hujan tinggi', 'location' => 'Bandung Barat', 'time' => '11 menit lalu'],
            ],
            'history' => [
                ['time' => '01:14 WIB', 'location' => 'Pasir Koja', 'status' => 'Banjir', 'confidence' => '94%'],
                ['time' => '01:12 WIB', 'location' => 'Kopo', 'status' => 'Waspada', 'confidence' => '87%'],
                ['time' => '01:09 WIB', 'location' => 'Gede Bage', 'status' => 'Aman', 'confidence' => '31%'],
                ['time' => '00:58 WIB', 'location' => 'Pasir Koja', 'status' => 'Waspada', 'confidence' => '82%'],
                ['time' => '00:42 WIB', 'location' => 'Kopo', 'status' => 'Aman', 'confidence' => '45%'],
                ['time' => '00:31 WIB', 'location' => 'Gede Bage', 'status' => 'Aman', 'confidence' => '28%'],
                ['time' => '00:18 WIB', 'location' => 'Pasir Koja', 'status' => 'Banjir', 'confidence' => '91%'],
            ],
        ], $page);
    }
}
