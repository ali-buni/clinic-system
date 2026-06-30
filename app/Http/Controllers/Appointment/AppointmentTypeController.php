<?php

namespace App\Http\Controllers\Appointment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\AddAppointmentTypeRequest;
use App\Http\Resources\Appointment\AppointmentTypeResource;
use App\Services\AppointmentTypeService;
use App\Services\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AppointmentTypeController extends Controller
{
    public function index(AppointmentTypeService $service): JsonResponse
    {
        try {
            $types = $service->index();

            return ApiResponse::success(AppointmentTypeResource::collection($types), 'Appointment types retrieved');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve appointment types', 500, config('app.debug') ? ['error' => $e->getMessage()] : null);
        }
    }

    public function add(AddAppointmentTypeRequest $request, AppointmentTypeService $service): JsonResponse
    {
        try {
            $data = $request->validated();
            $service->add($data);

            return ApiResponse::success(null, 'Appointment type created', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to create appointment type', 500, config('app.debug') ? ['error' => $e->getMessage()] : null);
        }
    }

    public function delete(int $id, AppointmentTypeService $service): JsonResponse
    {
        try {
            $service->delete($id);
            return ApiResponse::success(null, 'Appointment type deleted');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Appointment type not found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete appointment type', 500, config('app.debug') ? ['error' => $e->getMessage()] : null);
        }
    }
}
