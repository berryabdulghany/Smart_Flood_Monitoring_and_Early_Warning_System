<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Weather\OpenWeatherMapService;
use Illuminate\Http\JsonResponse;

class RealtimeWeatherController extends Controller
{
    public function __invoke(OpenWeatherMapService $weather): JsonResponse
    {
        $points = collect($this->monitoringPoints())
            ->mapWithKeys(function (array $point) use ($weather) {
                return [
                    $point['id'] => array_merge($point, [
                        'weather' => $weather->current($point['lat'], $point['lon'], $point['name']),
                    ]),
                ];
            });

        return response()->json([
            'overview' => $weather->current(-6.9175, 107.6191, 'Bandung City'),
            'points' => $points,
            'updated_at' => now('Asia/Jakarta')->toIso8601String(),
        ]);
    }

    private function monitoringPoints(): array
    {
        return [
            [
                'id' => 'kopo',
                'name' => 'Kopo',
                'lat' => -6.943258140459321,
                'lon' => 107.59159188077126,
            ],
            [
                'id' => 'pasir-koja',
                'name' => 'Pasir Koja',
                'lat' => -6.930461,
                'lon' => 107.575984,
            ],
            [
                'id' => 'gede-bage',
                'name' => 'Gede Bage',
                'lat' => -6.965528,
                'lon' => 107.686923,
            ],
        ];
    }
}
