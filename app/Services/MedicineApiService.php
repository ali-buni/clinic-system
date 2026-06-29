<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MedicineApiService
{
    public function searchMedicines(string $query): array
    {
        try {
            $response = Http::timeout(5)
                ->get('https://api.fda.gov/drug/ndc.json', [
                    'search' => "brand_name:\"{$query}\" OR generic_name:\"{$query}\"",
                    'limit' => 15
                ]);

            if ($response->successful()) {
                $data = $response->json()['results'] ?? [];

                $results = [];
                foreach ($data as $drug) {
                    $results[] = [
                        'api_medicine_id' => $drug['product_id'] ?? null,
                        'en_name' => $drug['brand_name'] ?? 'Unknown',
                        'ar_name' => null,
                        'generic_name_en' => is_array($drug['generic_name'] ?? null) ? implode(', ', $drug['generic_name']) : ($drug['generic_name'] ?? null),
                        'generic_name_ar' => null,
                        'strength' => isset($drug['active_ingredients'][0]['strength']) ? $drug['active_ingredients'][0]['strength'] : null,
                        'form' => $this->mapRouteToForm($drug['route'] ?? null),
                    ];
                }

                Log::channel('structured')->info('OpenFDA drug search succeeded', [
                    'query' => $query,
                    'results_count' => count($results),
                ]);

                return $results;
            }

            Log::channel('structured')->warning('OpenFDA drug search returned non-success', [
                'query' => $query,
                'status' => $response->status(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::channel('structured')->error('OpenFDA API Connection Failed', ['query' => $query, 'error' => $e->getMessage()]);
            return [];
        }
    }

    private function mapRouteToForm(?array $routes): string
    {
        if (empty($routes)) return 'tablet';

        $route = strtolower($routes[0]);

        if (str_contains($route, 'oral')) return 'tablet';
        if (str_contains($route, 'injection') || str_contains($route, 'intravenous')) return 'injection';
        if (str_contains($route, 'topical')) return 'ointment';

        return 'tablet';
    }
}
