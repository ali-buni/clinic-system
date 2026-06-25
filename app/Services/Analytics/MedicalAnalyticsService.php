<?php

namespace App\Services\Analytics;

use App\Models\Patient_record;
use App\Models\Disease;
use Illuminate\Support\Facades\DB;

class MedicalAnalyticsService
{
    public function getTopDiseases(int $clinicId, int $limit = 5)
    {
        return DB::table('patient_record_disease')
            ->join('patient_records', 'patient_record_disease.patient_record_id', '=', 'patient_records.id')
            ->join('diseases', 'patient_record_disease.disease_id', '=', 'diseases.id')
            ->where('patient_records.clinic_id', $clinicId)
            ->select('diseases.ar_name', DB::raw('count(*) as cases_count'))
            ->groupBy('diseases.id', 'diseases.ar_name')
            ->orderBy('cases_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getDiseasesByAgeGroup(int $clinicId)
    {
        return DB::table('patient_record_disease')
            ->join('patient_records', 'patient_record_disease.patient_record_id', '=', 'patient_records.id')
            ->join('patient_infos', 'patient_records.patient_id', '=', 'patient_infos.id')
            ->join('users', 'patient_infos.user_id', '=', 'users.id')
            ->join('diseases', 'patient_record_disease.disease_id', '=', 'diseases.id')
            ->where('patient_records.clinic_id', $clinicId)
            ->select('diseases.ar_name', DB::raw('TIMESTAMPDIFF(YEAR, users.dob, CURDATE()) as age'))
            ->get()
            ->groupBy(function($item) {
                if ($item->age < 30) return 'شباب';
                if ($item->age < 50) return 'بالغين';
                return 'كبار سن';
            });
    }
}
