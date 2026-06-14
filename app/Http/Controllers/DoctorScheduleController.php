<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkHourRequest;
use App\Services\DoctorScheduleService;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class DoctorScheduleController extends Controller
{
    protected $scheduleService;

    public function __construct(DoctorScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

 
    public function store(StoreWorkHourRequest $request): JsonResponse
    {
        $doctor = auth()->user()->doctorProfile;
        if (!$doctor) {
            return response()->json(['message' => 'Doctor profile not found.'], 404);
        }

        try {
            $workHour = $this->scheduleService->createWorkHour($doctor, $request->validated());
            
            return response()->json([
                'status' => true,
                'message' => 'Work hour added successfully.',
                'data' => $workHour,
            ], 201);

        } catch (Exception $e) {
            // نمسك الرموز اللي رميناها جوات السيرفس ونترجمها لردود
            $message = match($e->getMessage()) {
                'day_already_exists' => 'لا يمكن إضافة هذا اليوم. يوجد بالفعل دوام محدد لهذا اليوم!',
                'room_conflict'      => 'تعذر الإضافة. هذا الوقت يتضارب مع دوام طبيب آخر في نفس الغرفة!',
                default              => 'حدث خطأ غير متوقع.'
            };

            return response()->json([
                'status' => false,
                'message' => $message
            ], 422);
        }
    }

    
    public function update(StoreWorkHourRequest $request, $id): JsonResponse
    {
        $doctor = auth()->user()->doctorProfile;
        if (!$doctor) {
            return response()->json(['message' => 'Doctor profile not found.'], 404);
        }

        try {
            $workHour = $this->scheduleService->updateWorkHour($doctor, $id, $request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Work hour updated successfully.',
                'data' => $workHour
            ], 200);

        } catch (Exception $e) {
            // معالجة الأخطاء المرمية من السيرفس بشكل ذكي
            return match ($e->getMessage()) {
                'day_already_exists' => response()->json(['status' => false, 'message' => 'لا يمكن إضافة هذا اليوم. يوجد بالفعل دوام محدد لهذا اليوم!'], 422),
                'record_not_found' => response()->json(['status' => false, 'message' => 'Record not found or unauthorized.'], 404),
                'appointment_conflict' => response()->json(['status' => false, 'message' => 'تعذر التعديل. يوجد مواعيد مستقبلية مجدولة للمرضى ضمن هذا الوقت!'], 422),
                'room_conflict' => response()->json(['status' => false, 'message' => 'تعذر التعديل. الأوقات تتضارب مع طبيب آخر في نفس الغرفة!'], 422),
                default => response()->json(['status' => false, 'message' => 'حدث خطأ غير متوقع بالسيستم.'], 500),
            };
        }
    }

    public function destroy($id): JsonResponse
    {
        $doctor = auth()->user()->doctorProfile;
        if (!$doctor) {
            return response()->json(['message' => 'Doctor profile not found.'], 404);
        }

        try {
            $this->scheduleService->deleteWorkHour($doctor, $id);

            return response()->json([
                'status' => true,
                'message' => 'Work hour deleted successfully (Soft Deleted).'
            ], 200);

        } catch (Exception $e) {
            return match ($e->getMessage()) {
                'record_not_found' => response()->json(['status' => false, 'message' => 'Record not found or unauthorized.'], 404),
                'appointment_conflict' => response()->json(['status' => false, 'message' => 'لا يمكن حذف هذا اليوم. يوجد مواعيد مستقبلية مجدولة للمرضى في هذا الوقت!'], 422),
                default => response()->json(['status' => false, 'message' => 'حدث خطأ غير متوقع بالسيستم.'], 500),
            };
        }
    }

    public function getWeeklySchedule($userId): JsonResponse
    {
        $user = User::with('doctorProfile')->find($userId);
        if (!$user || !$user->doctorProfile) {
            return response()->json(['message' => 'Doctor profile not found.'], 404);
        }

        $schedule = $this->scheduleService->getDoctorWeeklySchedule($user->doctorProfile);

        return response()->json([
            'status' => true,
            'doctor_name' => $user->fname . ' ' . $user->lname,
            'schedule' => $schedule
        ], 200);
    }

  
/**
 * API عام لمعرفة ساعات دوام الدكتور في تاريخ محدد
 */
    public function getWorkHourByDate(Request $request, $userId): JsonResponse
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d'
        ]);

        $user = User::with('doctorProfile')->find($userId);
        if (!$user || !$user->doctorProfile) {
            return response()->json(['message' => 'Doctor profile not found.'], 404);
        }

        $workHour = $this->scheduleService->getDoctorWorkHourByDate(
            $user->doctorProfile, 
            $request->input('date') 
        );

        if (!$workHour) {
            return response()->json([
                'status' => false,
                'message' => 'الطبيب لا يداوم في هذا اليوم (يوم عطلة).'
            ], 200);
        }

        return response()->json([
            'status' => true,
            'date' => $request->input('date'),
            'work_hour' => $workHour
        ], 200);
    }

    public function getWorkingDays($userId): JsonResponse
    {
        $user = User::with('doctorProfile')->find($userId);
        if (!$user || !$user->doctorProfile) {
            return response()->json(['message' => 'Doctor profile not found.'], 404);
        }

        $workingDays = $this->scheduleService->getDoctorWorkingDays($user->doctorProfile);

        return response()->json([
            'status' => true,
            'working_days' => $workingDays // النتيجة رح تكون مصفوفة أرقام مثل [0, 2, 4]
        ], 200);
    }
}
