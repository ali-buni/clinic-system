<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\AppointmentAssistantRequest;
use App\Services\Ai\AppointmentAssistantService;
use App\Services\ApiResponse;
use Throwable;

class AppointmentAssistantController extends Controller
{
    public function __construct(protected AppointmentAssistantService $service) {}

    public function assist(AppointmentAssistantRequest $request)
    {
        try {
            $result = $this->service->processRequest($request->validated());

            if (!empty($result['error'])) {
                return ApiResponse::error($result['error'], 422);
            }

            return ApiResponse::success($result, 'Request processed');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
