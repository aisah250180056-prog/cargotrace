<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    /**
     * Mengambil data cuaca maritim dari Open-Meteo API (Slide 09)
     * Menggunakan Laravel HTTP Client, Timeout 4s, dan Cache 30 menit
     */
    public function getWeather(float $latitude, float $longitude): ?array
    {
        // Cache key unik berdasarkan koordinat
        $cacheKey = "weather_{$latitude}_{$longitude}";

        return Cache::remember($cacheKey, 1800, function () use ($latitude, $longitude) {
            try {
                // Request ke API cuaca dengan timeout ketat (Slide 09: tangani timeout & error)
                $response = Http::timeout(4)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude'      => $latitude,
                    'longitude'     => $longitude,
                    'current'       => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m',
                    'timezone'      => 'UTC',
                ]);

                if ($response->successful()) {
                    $current = $response->json('current');
                    $weatherCode = (int) ($current['weather_code'] ?? 0);

                    return [
                        'temperature'       => $current['temperature_2m'] ?? null,
                        'relative_humidity' => $current['relative_humidity_2m'] ?? null,
                        'wind_speed'        => $current['wind_speed_10m'] ?? null, // Kecepatan angin maritim (km/jam)
                        'condition'         => $this->mapWeatherCode($weatherCode),
                        'weather_code'      => $weatherCode,
                        'synced_at'         => now()->toDateTimeString(),
                    ];
                }

                Log::warning("Open-Meteo API returned non-200: " . $response->status());
                return null;
            } catch (Exception $e) {
                // Graceful fallback jika terjadi network error / timeout (Slide 09)
                Log::warning("External Weather API failure: " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Menerjemahkan WMO Weather Codes menjadi teks kondisi cuaca maritim
     */
    protected function mapWeatherCode(int $code): string
    {
        return match (true) {
            $code === 0       => 'Cerah (Clear Sky)',
            $code <= 3        => 'Sebagian Berawan (Partly Cloudy)',
            $code <= 48       => 'Berkabut (Foggy / Haze)',
            $code <= 67       => 'Hujan Ringan / Sedang (Rain)',
            $code <= 77       => 'Bersalju (Snow)',
            $code >= 95       => 'Badai Petir Maritim (Thunderstorm)',
            default           => 'Berawan (Cloudy)',
        };
    }
}