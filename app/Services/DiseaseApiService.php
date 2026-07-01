<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiseaseApiService
{
    public function searchDiseases(string $query): array
    {
        try {
            $response = Http::timeout(10)
                ->get('https://clinicaltables.nlm.nih.gov/api/icd10cm/v3/search', [
                    'terms' => $query,
                    'max'   => 20,
                    'sf'    => 'code,name',
                    'df'    => 'code,name',
                ]);

            if (!$response->successful()) {
                Log::channel('structured')->warning('ICD-10 search returned non-success', [
                    'query' => $query,
                    'status' => $response->status(),
                ]);
                return [];
            }

            $data = $response->json();
            if (empty($data[3])) {
                Log::channel('structured')->warning('ICD-10 search returned empty results', ['query' => $query]);
                return [];
            }

            $results = [];
            foreach ($data[3] as $index => $item) {
                $code = $data[1][$index] ?? $item[0] ?? null;
                $englishName = $item[1] ?? $item[0] ?? 'Unknown';

                $results[] = [
                    'code'            => $code,
                    'en_name'         => $englishName,
                    'ar_name'         => null,
                    'disease_nature'  => 'other',
                    'description'     => 'ICD-10 International Classification',
                ];
            }

            Log::channel('structured')->info('ICD-10 disease search succeeded', [
                'query' => $query,
                'results_count' => count($results),
            ]);

            return $results;
        } catch (\Exception $e) {
            Log::channel('structured')->error('ICD-10 Search Error', ['query' => $query, 'error' => $e->getMessage()]);
            return [];
        }
    }
}
