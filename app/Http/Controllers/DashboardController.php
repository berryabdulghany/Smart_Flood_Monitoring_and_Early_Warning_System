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
                'name' => 'Kopo',
                'district' => 'Bandung Kulon',
                'lat' => -6.943258140459321,
                'lng' => 107.59159188077126,
                'status' => 'warning',
                'status_label' => 'Waspada',
                'status_color' => '#f59e0b',
                'cctv' => 'BDG-KPO-01',
                'online' => true,
                'ai_confidence' => 87,
                'water_level' => 142,
                'rainfall' => 28,
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
                'name' => 'Pasir Koja',
                'district' => 'Bojongloa Kaler',
                'lat' => -6.930461,
                'lng' => 107.575984,
                'status' => 'danger',
                'status_label' => 'Banjir',
                'status_color' => '#ef4444',
                'cctv' => 'BDG-PSK-03',
                'online' => true,
                'ai_confidence' => 94,
                'water_level' => 188,
                'rainfall' => 41,
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
                'name' => 'Gede Bage',
                'district' => 'Gedebage',
                'lat' => -6.965528,
                'lng' => 107.686923,
                'status' => 'safe',
                'status_label' => 'Aman',
                'status_color' => '#22c55e',
                'cctv' => 'BDG-GDB-02',
                'online' => true,
                'ai_confidence' => 31,
                'water_level' => 72,
                'rainfall' => 12,
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
