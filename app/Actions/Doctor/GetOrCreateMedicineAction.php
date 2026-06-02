<?php

namespace App\Actions\Medicine;

use App\Models\Medicine;

class GetOrCreateMedicineAction
{
    public function execute(array $data): Medicine
    {
        if (!empty($data['api_medicine_id'])) {
            return Medicine::firstOrCreate(
                ['api_medicine_id' => $data['api_medicine_id']],
                [
                    'ar_name'         => $data['ar_name'] ?? null,
                    'en_name'         => $data['en_name'] ?? null,
                    'generic_name_ar' => $data['generic_name_ar'] ?? null,
                    'generic_name_en' => $data['generic_name_en'] ?? null,
                    'strength'        => $data['strength'] ?? null,
                    'form'            => $data['form'] ?? null,
                    'is_custom'       => false
                ]
            );
        }
        return Medicine::create([
            'ar_name'         => $data['ar_name'] ?? null,
            'en_name'         => $data['en_name'] ?? null,
            'generic_name_ar' => $data['generic_name_ar'] ?? null,
            'generic_name_en' => $data['generic_name_en'] ?? null,
            'strength'        => $data['strength'] ?? null,
            'form'            => $data['form'] ?? null,
            'is_custom'       => true
        ]);
    }
}
