<?php

namespace App\Actions\Medicine;

use App\Models\Medicine;
use Illuminate\Support\Facades\Log;

class GetOrCreateMedicineAction
{
    public function execute(array $data): Medicine
    {
        if (!empty($data['api_medicine_id'])) {

            $medicine = Medicine::firstOrCreate(
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

            if ($medicine->wasRecentlyCreated) {
                activity()
                    ->performedOn($medicine)
                    ->withProperties(['api_medicine_id' => $data['api_medicine_id'], 'source' => 'api_lookup'])
                    ->event('created')
                    ->log('medicine created via GetOrCreateMedicineAction');
                Log::channel('structured')->info('medicine created via GetOrCreateMedicineAction', [
                    'medicine_id' => $medicine->id, 'api_medicine_id' => $data['api_medicine_id'],
                ]);
            }

            return $medicine;
        }
        $existing = null;
        if (!empty($data['en_name']) || !empty($data['ar_name'])) {
            $dedupQuery = Medicine::query();
            if (!empty($data['en_name'])) {
                $dedupQuery->where('en_name', $data['en_name']);
            }
            if (!empty($data['ar_name'])) {
                $dedupQuery->orWhere('ar_name', $data['ar_name']);
            }
            $existing = $dedupQuery->first();
        }

        if ($existing) {
            return $existing;
        }

        $medicine = Medicine::create([
            'ar_name'         => $data['ar_name'] ?? null,
            'en_name'         => $data['en_name'] ?? null,
            'generic_name_ar' => $data['generic_name_ar'] ?? null,
            'generic_name_en' => $data['generic_name_en'] ?? null,
            'strength'        => $data['strength'] ?? null,
            'form'            => $data['form'] ?? null,
            'is_custom'       => true
        ]);

        activity()
            ->performedOn($medicine)
            ->withProperties(['en_name' => $data['en_name'] ?? null, 'source' => 'custom_create'])
            ->event('created')
            ->log('custom medicine created via GetOrCreateMedicineAction');
        Log::channel('structured')->info('custom medicine created via GetOrCreateMedicineAction', [
            'medicine_id' => $medicine->id, 'en_name' => $data['en_name'] ?? null,
        ]);

        return $medicine;
    }
}
