<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClinicAnalyticsSnapshot;
use App\Services\Analytics\{OperationalService, FinancialService, PatientAnalyticsService, MedicalAnalyticsService, PredictiveService, NLAService, ClinicHealthScoreService};
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function getOperationalReport(Request $request, OperationalService $ops)
    {
        $clinicId = $request->user()->clinicOwner->id;
        $date = $request->input('date', date('Y-m-d'));
        return response()->json(['status' => 'success', 'data' => $ops->getDoctorUtilization($clinicId, $date)]);
    }

    public function getFinancialReport(Request $request, FinancialService $fin)
    {
        $clinicId = $request->user()->clinicOwner->id;
        return response()->json(['status' => 'success', 'data' => $fin->getRevenueOverview($clinicId)]);
    }

    public function getPatientReport(Request $request, PatientAnalyticsService $pat)
    {
        $clinicId = $request->user()->clinicOwner->id;
        return response()->json([
            'status' => 'success',
            'retention' => $pat->getRetentionMetrics($clinicId),
            'lost_patients' => $pat->getLostPatients($clinicId)
        ]);
    }

    public function getMedicalReport(Request $request, MedicalAnalyticsService $med)
    {
        $clinicId = $request->user()->clinicOwner->id;
        return response()->json([
            'status' => 'success',
            'top_diseases' => $med->getTopDiseases($clinicId),
            'by_age' => $med->getDiseasesByAgeGroup($clinicId)
        ]);
    }

    public function getPredictiveReport(Request $request, PredictiveService $pre)
    {
        $clinicId = $request->user()->clinicOwner->id;
        return response()->json([
            'status' => 'success',
            'crowding_risk' => $pre->getCrowdingRisk($clinicId)
        ]);
    }

    public function askAnalytics(Request $request, NLAService $nla, OperationalService $ops, FinancialService $fin, MedicalAnalyticsService $med)
{
    $request->validate(['question' => 'required|string']);
    $clinicId = $request->user()->clinicOwner->id;

    $contextData = [
        'operations' => $ops->getDoctorUtilization($clinicId, date('Y-m-d')),
        'financials' => $fin->getRevenueOverview($clinicId),
        'medical'    => $med->getTopDiseases($clinicId),
    ];

    $contextString = json_encode($contextData, JSON_PRETTY_PRINT);

    return response()->json([
        'answer' => $nla->askAnalytics($request->question, $contextString)
    ]);
}

    public function getHealthScore(Request $request, ClinicHealthScoreService $scoreService)
    {
        return response()->json($scoreService->calculateScore($request->user()->clinicOwner->id));
    }

    public function getDashboard(Request $request, OperationalService $ops, FinancialService $fin, PatientAnalyticsService $pat)
    {
        $clinicId = $request->user()->clinicOwner->id;
        return response()->json([
            'operational' => $ops->getDoctorUtilization($clinicId, date('Y-m-d')),
            'financial' => $fin->getRevenueOverview($clinicId),
            'retention' => $pat->getRetentionMetrics($clinicId),
        ]);
    }

    public function storeSnapshot(Request $request, OperationalService $ops)
    {
        $clinicId = $request->user()->clinicOwner->id;
        $data = $ops->getDoctorUtilization($clinicId, date('Y-m-d'));

        ClinicAnalyticsSnapshot::create([
            'clinic_id' => $clinicId,
            'metric_name' => 'doctor_utilization',
            'data' => $data,
            'snapshot_date' => now()
        ]);

        return response()->json(['message' => 'Snapshot saved successfully']);
    }
}
