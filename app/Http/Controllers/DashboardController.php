<?php

namespace App\Http\Controllers;

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

    public function weather(): View
    {
        return view('pages.monitoring.weather', $this->dashboardData([
            'pageTitle' => 'Weather Monitoring',
            'pageSubtitle' => 'BMKG weather intelligence and short-term flood prediction.',
        ]));
    }

    public function history(): View
    {
        return view('pages.monitoring.history', $this->dashboardData([
            'pageTitle' => 'Detection History',
            'pageSubtitle' => 'Searchable AI flood detection event log across monitoring points.',
        ]));
    }

    public function settings(): View
    {
        return view('pages.monitoring.settings', $this->dashboardData([
            'pageTitle' => 'Settings',
            'pageSubtitle' => 'System configuration for CCTV, AI threshold, sensors, and API integrations.',
        ]));
    }

    private function dashboardData(array $page = []): array
    {
        $locations = [
            [
                'id' => 'kopo',
                'name' => 'KOPO - RS Bandung Kiwari 02',
                'short_name' => 'Kopo',
                'district' => 'RS Bandung Kiwari 02',
                'lat' => -6.943258140459321,
                'lng' => 107.59159188077126,
                'status' => 'warning',
                'status_label' => 'Waspada',
                'status_color' => '#f59e0b',
                'cctv' => 'BDG-KPO-01',
                'cctv_live_url' => 'https://pelindung.bandung.go.id:3443/video/DPU/kopocitarip.m3u8',
                'cctv_fallback_url' => '/videos/kopo-flood-transition.mp4',
                'online' => true,
                'ai_confidence' => 62,
                'water_level' => 10,
                'rainfall' => 2.1,
                'temperature' => 25.7,
                'humidity' => 86,
                'weather' => 'Hujan sedang',
                'rain_potential' => '78%',
                'flood_prediction' => 'Waspada 90 menit',
                'last_detection' => '01:12 WIB',
                'wind_speed' => 9,
                'battery' => 91,
            ],
            [
                'id' => 'pasir-koja',
                'name' => 'SP Pasir Koja',
                'short_name' => 'Pasir Koja',
                'district' => 'Bojongloa Kaler',
                'lat' => -6.930461,
                'lng' => 107.575984,
                'status' => 'danger',
                'status_label' => 'Banjir',
                'status_color' => '#ef4444',
                'cctv' => 'BDG-PSK-03',
                'cctv_live_url' => 'https://pelindung.bandung.go.id:3443/video/DISHUB/sppasirkoja.m3u8',
                'cctv_fallback_url' => '/videos/pasirkoja-flood-transition.mp4',
                'online' => true,
                'ai_confidence' => 86,
                'water_level' => 18,
                'rainfall' => 4.3,
                'temperature' => 25.1,
                'humidity' => 91,
                'weather' => 'Hujan lebat',
                'rain_potential' => '92%',
                'flood_prediction' => 'Tinggi 45 menit',
                'last_detection' => '01:14 WIB',
                'wind_speed' => 14,
                'battery' => 86,
            ],
            [
                'id' => 'gede-bage',
                'name' => 'Gedebage Selatan - Jl. Derwati',
                'short_name' => 'Gede Bage',
                'district' => 'Jl. Derwati',
                'lat' => -6.965528,
                'lng' => 107.686923,
                'status' => 'safe',
                'status_label' => 'Aman',
                'status_color' => '#22c55e',
                'cctv' => 'BDG-GDB-02',
                'cctv_live_url' => 'https://pelindung.bandung.go.id:3443/video/HIKSVISION/rancanumpangg.m3u8',
                'cctv_fallback_url' => '/videos/gedebage-flood-transition.mp4',
                'online' => true,
                'ai_confidence' => 31,
                'water_level' => 3,
                'rainfall' => 0,
                'temperature' => 26.4,
                'humidity' => 79,
                'weather' => 'Berawan',
                'rain_potential' => '36%',
                'flood_prediction' => 'Rendah 2 jam',
                'last_detection' => '01:09 WIB',
                'wind_speed' => 7,
                'battery' => 96,
            ],
        ];

        return array_merge([
            'locations' => $locations,
            'stats' => [
                'active_cctv' => 3,
                'active_sensors' => 9,
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
