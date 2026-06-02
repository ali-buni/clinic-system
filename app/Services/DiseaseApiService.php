<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiseaseApiService
{
    public function searchDiseases(string $query): array
    {
        try {
            $response = Http::timeout(5)
                ->get('https://clinicaltables.nlm.nih.gov/api/icd10cm/v3/search', [
                    'terms' => $query,
                    'max'   => 15
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $results = [];
                if (isset($data[3])) {
                    foreach ($data[3] as $index => $diseaseInfo) {
                        $results[] = [
                            'code'            => $data[1][$index] ?? null,
                            'en_name'         => $diseaseInfo[0] ?? 'Unknown',
                            'ar_name'         => $diseaseInfo[0],
                            'disease_nature'  => 'other',
                            'description'     => 'ICD-10 International Classification',
                        ];
                    }
                }
                return $results;
            }

            return [];
        } catch (\Exception $e) {
            Log::error('ICD-10 API Connection Failed', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
