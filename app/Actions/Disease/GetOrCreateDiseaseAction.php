<?php

namespace App\Actions\Disease;

use App\Models\Disease;

class GetOrCreateDiseaseAction
{
    public function execute(array $data): Disease
    {
        if (!empty($data['code'])) {
            return Disease::firstOrCreate(
                ['code' => $data['code']],
                [
                    'ar_name'        => $data['ar_name'],
                    'en_name'        => $data['en_name'],
                    'description'    => $data['description'] ?? null,
                    'disease_nature' => $data['disease_nature'] ?? 'other',
                    'is_custom'      => false
                ]
            );
        }

        return Disease::firstOrCreate(
            [
                'ar_name' => $data['ar_name'],
                'en_name' => $data['en_name'],
            ],
            [
                'code'           => null,
                'description'    => $data['description'] ?? null,
                'disease_nature' => $data['disease_nature'] ?? 'other',
                'is_custom'      => true
            ]
        );
    }
}
