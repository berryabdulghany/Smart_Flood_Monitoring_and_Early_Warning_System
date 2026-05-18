<?php

namespace App\Services\Weather;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class OpenWeatherMapService
{
    public function current(float $lat, float $lon, string $label): array
    {
        $cacheKey = 'weather:openweather:'.md5($label.'|'.$lat.'|'.$lon);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($lat, $lon, $label) {
            $apiKey = config('services.openweather.key');

            if (blank($apiKey)) {
                return $this->fallback($label, 'OPENWEATHER_API_KEY belum diisi.');
            }

            try {
                $payload = Http::timeout(8)
                    ->retry(1, 250)
                    ->get(rtrim(config('services.openweather.base_url'), '/').'/weather', [
                        'lat' => $lat,
                        'lon' => $lon,
                        'appid' => $apiKey,
                        'units' => 'metric',
                        'lang' => 'id',
                    ])
                    ->throw()
                    ->json();

                return $this->normalize($payload, $label);
            } catch (Throwable $exception) {
                report($exception);

                return $this->fallback($label, 'OpenWeatherMap tidak dapat diakses.');
            }
        });
    }

    private function normalize(array $payload, string $label): array
    {
        $weather = $payload['weather'][0] ?? [];
        $icon = $weather['icon'] ?? null;
        $rain = $payload['rain']['1h'] ?? $payload['rain']['3h'] ?? 0;

        return [
            'label' => $label,
            'temperature' => round((float) ($payload['main']['temp'] ?? 0), 1),
            'humidity' => (int) ($payload['main']['humidity'] ?? 0),
            'condition' => ucfirst((string) ($weather['description'] ?? 'Tidak tersedia')),
            'condition_main' => (string) ($weather['main'] ?? 'Unknown'),
            'wind_speed' => round(((float) ($payload['wind']['speed'] ?? 0)) * 3.6, 1),
            'rainfall' => round((float) $rain, 1),
            'icon' => $icon,
            'icon_url' => $icon ? "https://openweathermap.org/img/wn/{$icon}@2x.png" : null,
            'source' => 'OpenWeatherMap',
            'is_realtime' => true,
            'message' => null,
            'fetched_at' => now('Asia/Jakarta')->toIso8601String(),
        ];
    }

    private function fallback(string $label, string $message): array
    {
        return [
            'label' => $label,
            'temperature' => null,
            'humidity' => null,
            'condition' => 'Weather unavailable',
            'condition_main' => 'Unavailable',
            'wind_speed' => null,
            'rainfall' => null,
            'icon' => null,
            'icon_url' => null,
            'source' => 'Fallback',
            'is_realtime' => false,
            'message' => $message,
            'fetched_at' => now('Asia/Jakarta')->toIso8601String(),
        ];
    }
}
