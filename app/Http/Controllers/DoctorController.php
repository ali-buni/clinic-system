<?php

namespace App\Http\Controllers;

use App\Actions\Doctor\CreateDoctorAction;
use App\Actions\Doctor\UpdateDoctorAction;
use App\Actions\Doctor\DeleteDoctorAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\Request;
use App\Services\ModelFilter;

class DoctorController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }
    public function store(StoreDoctorRequest $request, CreateDoctorAction $action): JsonResponse
    {
        $owner = $request->user();

        if (!$owner->clinicOwner?->id) {
            return response()->json([
                'message' => 'The current account is not associated with any clinic.'
            ], 400);
        }

        try {
            $doctor = $action->execute(
                $request->validated(),
                $owner->clinicOwner->id
            );

            return response()->json([
                'message' => 'Doctor created successfully.',
                'doctor'  => new DoctorResource($doctor->load('user'))
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create doctor', [
                'user_id'=> $owner->id,
                'error'=> $e->getMessage()
            ]);

            return response()->json([
                'message'=> 'Failed to create doctor.',
                'error'=> config('app.debug') ? $e->getMessage() : null,], 500);
        }
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor, UpdateDoctorAction $action): JsonResponse
    {
        //Gate::authorize('update', $doctor);

        try {
            $updatedDoctor = $action->execute(
                $doctor,
                $request->validated()
            );

            return response()->json([
                'message' => 'Your profile has been updated successfully.',
                'doctor'  => new DoctorResource($updatedDoctor->load('user'))
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to update doctor', [
                'doctor_id' => $doctor->id,
                'error'     => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to update doctor.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
    public function destroy(Doctor $doctor, DeleteDoctorAction $action): JsonResponse
    {
        //Gate::authorize('delete', $doctor);

        try {
            $action->execute($doctor);

            return response()->json([
                'message' => 'The doctor has successfully left the clinic, and their profile has been removed.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to delete doctor', [
                'doctor_id'=> $doctor->id,
                'error'=> $e->getMessage()
            ]);

            return response()->json([
                'message'=> $e->getMessage() ?: 'Failed to complete the action.',
            ], 500);
        }
    }

    public function show(Doctor $doctor): JsonResponse
    {
        Gate::authorize('view', $doctor);

        return response()->json([
            'doctor'=> new DoctorResource($doctor->load('user'))
        ], 200);
    }

    public function clinicDoctors(Request $request): JsonResponse
    {
        $owner = $request->user();
        $clinicId = $owner->clinicOwner?->id;

        if (!$clinicId) {
            return response()->json([
                'message'=> 'The current account is not associated with any clinic.'
            ], 400);
        }

        $doctors = Doctor::where('clinic_id', $clinicId)
            ->with('user')
            ->get();

        return response()->json([
            'doctors'=> DoctorResource::collection($doctors)
        ], 200);
    }

    public function roomDoctors(Request $request, Room $room): JsonResponse
    {
        $owner = $request->user();
        $clinicId = $owner->clinicOwner?->id;

        if ($room->clinic_id !== $clinicId) {
            return response()->json([
                'message'=> 'Unauthorized. This room does not belong to your clinic.'
            ], 403);
        }

        $doctors = Doctor::where('room_id', $room->id)
            ->with('user')
            ->get();

        return response()->json([
            'room_name'=> $room->name ?? "Room #{$room->id}",
            'doctors'=> DoctorResource::collection($doctors)
        ], 200);
    }
    public function index(Request $request): JsonResponse
    {
        $owner = $request->user();
        $clinicId = $owner->clinicOwner?->id;

        if (!$clinicId) {
            return response()->json([
                'message' => 'The current account is not associated with any clinic.'
            ], 400);
        }

        $secureQuery = Doctor::where('clinic_id', $clinicId)->with('user');

        $result = ModelFilter::filter($secureQuery, $request->all());

        return response()->json([
            'doctors' => DoctorResource::collection($result->items()),
            'meta' => [
                'current_page' => $result->currentPage(),
                'last_page'    => $result->lastPage(),
                'total'        => $result->total(),
            ]
        ], 200);
    }
}
