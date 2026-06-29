<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\SummarizeReportRequest;
use App\Services\Ai\MedicalReportService;
use App\Services\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class MedicalReportController extends Controller
{
    public function __construct(private MedicalReportService $service) {}

    public function summarize(SummarizeReportRequest $request)
    {
        $validated = $request->validated();

        try {
            $summary = $this->service->summarize($validated['record_id']);
            if (!$summary) {
                return ApiResponse::error('AI service is currently unavailable', 503);
            }
            return ApiResponse::success($summary, 'Report summarized successfully');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Patient record not found', 404);
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
