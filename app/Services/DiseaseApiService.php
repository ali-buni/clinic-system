<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
            return [];
        }

        $data = $response->json();
        if (empty($data[3])) {
            return [];
        }

        $results = [];
        foreach ($data[3] as $index => $item) {
            $code = $data[1][$index] ?? $item[0] ?? null;
            $englishName = $item[1] ?? $item[0] ?? 'Unknown';

            // Get Arabic name from your database
            $arabicName = $this->getArabicName($code, $englishName);

            $results[] = [
                'code'            => $code,
                'en_name'         => $englishName,
                'ar_name'         => $arabicName,
                'disease_nature'  => 'other',
                'description'     => 'ICD-10 International Classification',
            ];
        }

        return $results;

    } catch (\Exception $e) {
        Log::error('ICD-10 Search Error', ['query' => $query, 'error' => $e->getMessage()]);
        return [];
    }
}

// Helper method
private function getArabicName(string $code, string $fallback): string
{
    $translation = DB::table('icd10_translations')
        ->where('code', $code)
        ->first();

    return $translation?->ar_name ?? $fallback;
}
}
