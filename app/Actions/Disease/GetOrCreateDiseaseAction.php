<?php

namespace App\Actions\Disease;

use App\Models\Disease;
use Illuminate\Support\Facades\Log;

class GetOrCreateDiseaseAction
{
    public function execute(array $data): Disease
    {
        if (!empty($data['code'])) {
            $disease = Disease::firstOrCreate(
                ['code' => $data['code']],
                [
                    'ar_name'        => $data['ar_name'],
                    'en_name'        => $data['en_name'],
                    'description'    => $data['description'] ?? null,
                    'disease_nature' => $data['disease_nature'] ?? 'other',
                    'is_custom'      => false
                ]
            );

            if ($disease->wasRecentlyCreated) {
                activity()
                    ->performedOn($disease)
                    ->withProperties(['code' => $data['code'], 'source' => 'api_lookup'])
                    ->event('created')
                    ->log('disease created via GetOrCreateDiseaseAction');
                Log::channel('structured')->info('disease created via GetOrCreateDiseaseAction', [
                    'disease_id' => $disease->id, 'code' => $data['code'],
                ]);
            }

            return $disease;
        }

        $disease = Disease::firstOrCreate(
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

        if ($disease->wasRecentlyCreated) {
            activity()
                ->performedOn($disease)
                ->withProperties(['en_name' => $data['en_name'], 'source' => 'custom_create'])
                ->event('created')
                ->log('custom disease created via GetOrCreateDiseaseAction');
            Log::channel('structured')->info('custom disease created via GetOrCreateDiseaseAction', [
                'disease_id' => $disease->id, 'en_name' => $data['en_name'],
            ]);
        }

        return $disease;
    }
}
