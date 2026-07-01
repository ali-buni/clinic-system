<?php

namespace App\Services\Analytics;

use App\Support\DatabaseHelper;
use Illuminate\Support\Facades\DB;

class MedicalAnalyticsService
{
    public function getTopDiseases(int $clinicId, int $limit = 5): \Illuminate\Support\Collection
    {
        return DB::table('patient_record_disease as prd')
            ->join('patient_records as pr', 'prd.patient_record_id', '=', 'pr.id')
            ->join('diseases as d', 'prd.disease_id', '=', 'd.id')
            ->where('pr.clinic_id', $clinicId)
            ->select(['d.ar_name', 'd.en_name'])
            ->selectRaw('COUNT(*) as cases_count')
            ->groupBy('d.id', 'd.ar_name')
            ->orderByDesc('cases_count')
            ->limit($limit)
            ->get();
    }

    public function getDiseasesByAgeGroup(int $clinicId): \Illuminate\Support\Collection
    {
        return DB::table('patient_record_disease as prd')
            ->join('patient_records as pr', 'prd.patient_record_id', '=', 'pr.id')
            ->join('patient_infos as pi', 'pr.patient_id', '=', 'pi.id')
            ->join('users as u', 'pi.user_id', '=', 'u.id')
            ->join('diseases as d', 'prd.disease_id', '=', 'd.id')
            ->where('pr.clinic_id', $clinicId)
            ->select(['d.ar_name', 'd.en_name'])
            ->selectRaw("
            CASE
                WHEN " . DatabaseHelper::age('u.dob') . " < 30 THEN 'شباب'
                WHEN " . DatabaseHelper::age('u.dob') . " < 50 THEN 'بالغين'
                ELSE 'كبار سن'
            END as age_group
        ")
            ->selectRaw('COUNT(*) as cases_count')
            ->groupBy('d.id', 'd.ar_name', 'd.en_name', 'age_group')
            ->orderBy('age_group')
            ->orderByDesc('cases_count')
            ->get()
            ->groupBy('age_group');
    }
}
