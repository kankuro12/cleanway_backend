<?php

namespace App\Services\Geocoding;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Backend-only Google Places / Geocoding client. The API key lives in the
 * server config and is never exposed to JavaScript.
 */
class GooglePlaces
{
    private ?string $key;

    private string $baseUrl;

    private string $region;

    private int $timeout;

    public function __construct()
    {
        $this->key = config('services.google_places.key') ?: null;
        $this->baseUrl = rtrim((string) config('services.google_places.url'), '/');
        $this->region = (string) config('services.google_places.region');
        $this->timeout = (int) config('services.google_places.timeout');
    }

    public function configured(): bool
    {
        return $this->key !== null;
    }

    /**
     * Place Autocomplete suggestions.
     *
     * @return array<int, array{place_id: string, description: string}>
     */
    public function autocomplete(string $input): array
    {
        if (! $this->configured() || trim($input) === '') {
            return [];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/place/autocomplete/json", [
                    'input' => $input,
                    'key' => $this->key,
                    'region' => $this->region,
                    'components' => 'country:nz',
                ]);
        } catch (ConnectionException) {
            return [];
        }

        if ($response->failed() || ($response->json('status') ?? '') !== 'OK') {
            return [];
        }

        return collect($response->json('predictions', []))->map(
            fn (array $p) => [
                'place_id' => $p['place_id'],
                'description' => $p['description'],
            ]
        )->all();
    }

    /**
     * Place Details for a selected suggestion.
     *
     * @return array{place_id: string, formatted_address: string, latitude: float, longitude: float, accuracy: string}|null
     */
    public function placeDetails(string $placeId): ?array
    {
        if (! $this->configured()) {
            return null;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/place/details/json", [
                    'place_id' => $placeId,
                    'key' => $this->key,
                    'fields' => 'place_id,formatted_address,geometry',
                ]);
        } catch (ConnectionException) {
            return null;
        }

        if ($response->failed() || ($response->json('status') ?? '') !== 'OK') {
            return null;
        }

        $result = $response->json('result', []);
        $location = $result['geometry']['location'] ?? [];

        if (! isset($location['lat'], $location['lng'])) {
            return null;
        }

        return [
            'place_id' => $result['place_id'] ?? $placeId,
            'formatted_address' => $result['formatted_address'] ?? null,
            'latitude' => (float) $location['lat'],
            'longitude' => (float) $location['lng'],
            'accuracy' => ($result['geometry']['location_type'] ?? 'unknown') === 'ROOFTOP' ? 'rooftop' : 'approximate',
        ];
    }

    /**
     * Geocoding fallback (used by the queued resolution job).
     *
     * @return array<int, array{formatted_address: string, latitude: float, longitude: float, accuracy: string, score: float}>
     */
    public function geocode(string $query): array
    {
        if (! $this->configured() || trim($query) === '') {
            return [];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/geocode/json", [
                    'address' => $query,
                    'key' => $this->key,
                    'region' => $this->region,
                ]);
        } catch (ConnectionException) {
            return [];
        }

        if ($response->failed() || ($response->json('status') ?? '') !== 'OK') {
            return [];
        }

        $results = collect($response->json('results', []));

        // Rough relevance score: prefer rooftop precision and shorter formatted
        // addresses (fewer components means the query matched more closely).
        $max = max(1, $results->count());

        return $results->map(function (array $r) use ($max): array {
            $location = $r['geometry']['location'] ?? [];
            $rooftop = ($r['geometry']['location_type'] ?? '') === 'ROOFTOP';

            return [
                'formatted_address' => $r['formatted_address'] ?? '',
                'latitude' => (float) ($location['lat'] ?? 0),
                'longitude' => (float) ($location['lng'] ?? 0),
                'accuracy' => $rooftop ? 'rooftop' : 'approximate',
                'score' => round((($rooftop ? 100 : 60) + ($max - $this->indexOf($results, $r)) * 5) / $max, 2),
            ];
        })->sortByDesc('score')->values()->all();
    }

    private function indexOf($collection, array $result): int
    {
        $index = 0;

        foreach ($collection as $i => $item) {
            if ($item === $result) {
                return $i;
            }
            $index = $i;
        }

        return $index;
    }
}
