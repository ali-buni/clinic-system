<?php

namespace App\Services\Ai;

use App\constant\KeyWordHelper;
use App\constant\Prompt;
use App\Models\Specialty;
use App\Services\Ai\Contracts\AiProviderInterface;

class SpecialtyMatcher
{
    private array $keywordMap = KeyWordHelper::SPECIALTY_HELPER_WORD;
    public function __construct(protected AiProviderInterface $ai) {}

    public function suggest(string $query): array
    {
        $allSpecialties = Specialty::select('id', 'en_name', 'ar_name')->get();

        $direct = $this->matchDirectly($query, $allSpecialties);
        if (!empty($direct)) {
            return [
                'specialties' => $this->localize($direct, $query),
                'next_step' => 'select_specialty',
            ];
        }

        $ai = $this->matchWithAi($query, $allSpecialties);
        if (!empty($ai['specialties'])) {
            return $ai;
        }

        $fallback = $this->matchFallback($query, $allSpecialties);
        if (!empty($fallback['specialties'])) {
            return $fallback;
        }

        return $this->fullListResponse($allSpecialties, $query);
    }

    public function isArabic(string $text): bool
    {
        return preg_match('/\p{Arabic}/u', $text) === 1;
    }

    private function matchDirectly(string $query, $allSpecialties): array
    {
        $results = [];
        $queryLower = mb_strtolower(trim($query));

        foreach ($allSpecialties as $specialty) {
            $score = 0;
            $enLower = mb_strtolower($specialty->en_name);
            $arLower = mb_strtolower($specialty->ar_name);

            if (str_contains($enLower, $queryLower) || str_contains($queryLower, $enLower)) {
                $score += 10;
            }
            if (str_contains($arLower, $queryLower) || str_contains($queryLower, $arLower)) {
                $score += 10;
            }

            foreach (explode(' ', $enLower) as $word) {
                if (strlen($word) > 2 && str_contains($queryLower, $word)) {
                    $score += 5;
                }
            }

            foreach ($this->keywordMap as $word => $mapped) {
                if ($mapped !== $specialty->en_name) {
                    continue;
                }
                if (str_contains($queryLower, $word)) {
                    $score += 8;
                }
            }

            if ($score > 0) {
                $results[] = [
                    'id' => $specialty->id,
                    'en_name' => $specialty->en_name,
                    'ar_name' => $specialty->ar_name,
                    'match_score' => $score,
                ];
            }
        }

        usort($results, fn($a, $b) => $b['match_score'] - $a['match_score']);

        return array_slice($results, 0, 5);
    }

    private function matchWithAi(string $query, $allSpecialties): array
    {
        $specialtyList = $allSpecialties->map(fn($s) => [
            'id' => $s->id,
            'en_name' => $s->en_name,
            'ar_name' => $s->ar_name,
        ])->values()->toJson(JSON_PRETTY_PRINT);

        $keywordRef = '';
        foreach ($this->keywordMap as $word => $specialty) {
            $keywordRef .= "- \"$word\" → $specialty\n";
        }

        $isAr = $this->isArabic($query);
        $systemPrompt = $isAr
            ? Prompt::SELECT_SPECIAL_AR($specialtyList, $keywordRef)
            : Prompt::SELECT_SPECIAL_EN($specialtyList, $keywordRef);

        $parsed = $this->ai->chatJson(
            messages: [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $query],
            ],
            options: [
                'options' => [
                    'num_ctx' => 2048,
                    'temperature' => 0.3,
                ],
            ]
        );

        if (!$parsed || empty($parsed['specialties'])) {
            return ['error' => $isAr ? 'تعذر تحديد التخصص المناسب من وصفك.' : 'Could not determine specialty from your description.', 'next_step' => 'retry'];
        }

        $validIds = $allSpecialties->pluck('id')->toArray();
        $filtered = array_filter($parsed['specialties'], fn($s) => in_array($s['specialty_id'] ?? 0, $validIds));

        if (empty($filtered)) {
            $nameToId = $allSpecialties->keyBy(fn($s) => strtolower($s->en_name))->map->id->toArray();
            foreach ($parsed['specialties'] as $s) {
                $en = strtolower(trim($s['en_name'] ?? ''));
                if (isset($nameToId[$en])) {
                    $s['specialty_id'] = $nameToId[$en];
                    $filtered[] = $s;
                }
            }
        }

        if (empty($filtered)) {
            return [];
        }

        return [
            'specialties' => array_map(fn($s) => [
                'id' => (int) $s['specialty_id'],
                'name' => $isAr ? ($s['ar_name'] ?? $s['en_name'] ?? 'غير معروف') : ($s['en_name'] ?? 'Unknown'),
                'en_name' => $s['en_name'] ?? 'Unknown',
                'ar_name' => $s['ar_name'] ?? 'غير معروف',
                'reason' => $s['reason'] ?? '',
            ], array_values($filtered)),
            'next_step' => 'select_specialty',
        ];
    }

    private function matchFallback(string $query, $allSpecialties): array
    {
        $queryWords = preg_split('/[\s,.-]+/', mb_strtolower(trim($query)));
        $isAr = $this->isArabic($query);
        $scored = [];

        foreach ($allSpecialties as $specialty) {
            $score = 0;
            $en = mb_strtolower($specialty->en_name);
            $ar = mb_strtolower($specialty->ar_name);

            foreach ($queryWords as $word) {
                if (strlen($word) <= 2) continue;
                if (str_contains($en, $word) || str_contains($ar, $word)) $score += 3;
                foreach ($this->keywordMap as $kw => $mapped) {
                    if ($mapped === $specialty->en_name && str_contains($word, $kw)) {
                        $score += 5;
                    }
                }
            }

            if ($score > 0) {
                $scored[] = [
                    'id' => $specialty->id,
                    'en_name' => $specialty->en_name,
                    'ar_name' => $specialty->ar_name,
                    'match_score' => $score,
                ];
            }
        }

        if (empty($scored)) return [];

        usort($scored, fn($a, $b) => $b['match_score'] - $a['match_score']);

        return [
            'specialties' => $this->localize(array_slice($scored, 0, 5), $query),
            'next_step' => 'select_specialty',
        ];
    }

    private function fullListResponse($allSpecialties, string $query): array
    {
        $isAr = $this->isArabic($query);
        return [
            'specialties' => $allSpecialties->map(fn($s) => [
                'id' => $s->id,
                'name' => $isAr ? $s->ar_name : $s->en_name,
                'en_name' => $s->en_name,
                'ar_name' => $s->ar_name,
            ])->values()->toArray(),
            'next_step' => 'select_specialty',
        ];
    }

    private function localize(array $specialties, string $query): array
    {
        $isAr = $this->isArabic($query);
        return array_map(fn($s) => [
            'id' => $s['id'],
            'name' => $isAr ? $s['ar_name'] : $s['en_name'],
            'en_name' => $s['en_name'],
            'ar_name' => $s['ar_name'],
            'match_score' => $s['match_score'],
        ], $specialties);
    }
}
