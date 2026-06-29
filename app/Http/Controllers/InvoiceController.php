<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreateInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Requests\DeleteInvoiceRequest;
use App\Services\InvoiceService;
use App\Services\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Models\Invoice;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{

    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function createInvoice(CreateInvoiceRequest $request): JsonResponse
    {

        $invoice = $this->invoiceService->createInvoice($request->validated());

        $responseData = [
            'invoice_number' => $invoice->invoice_number,
            'total_cost'     => $invoice->total_cost,
            'status'         => 'draft',
        ];
        return ApiResponse::success($responseData, 'تم إنشاء الفاتورة وحساب قيمتها الإجمالية تلقائياً.', 201);
    }

    public function updateInvoice(UpdateInvoiceRequest $request): JsonResponse
    {
        // تمرير البيانات المفلترة من الـ Form Request إلى السيرفس
        $invoice = $this->invoiceService->updateInvoice($request->validated());

        // بناء الداتا المطلوبة بالملي في وثيقة المواصفات
        $responseData = [
            'invoice_number' => $invoice->invoice_number,
            'total_cost'     => $invoice->total_cost,
            'status'         => $invoice->status,
        ];

        // إرجاع الرد الموحد
        return ApiResponse::success($responseData, 'تم تعديل الفاتورة وإعادة احتساب قيمتها الإجمالية بنجاح.');
    }

    public function deleteInvoice(DeleteInvoiceRequest $request): JsonResponse
    {

        $invoiceId = $request->input('invoice_id');

        $this->invoiceService->deleteInvoice($invoiceId);

        return ApiResponse::success(null, 'تم حذف الفاتورة بنجاح.');
    }

    public function getPatientInvoices($patient_id)
    {

        $invoices = $this->invoiceService->getPatientInvoices($patient_id);

        return ApiResponse::success($invoices, 'تم جلب فواتير المريض بنجاح.');
    }
    public function getRoomsInvoices(Request $request)
    {
        // 1. استقبل مصفوفة الـ room_ids وتأكد إنها مصفوفة وغير فارغة
        $roomIds = $request->input('room_ids');

        if (!$roomIds || !is_array($roomIds)) {
            return ApiResponse::error("يجب تمرير مصفوفة تحتوي على معرفات الغرف (room_ids).", 400);
        }

        try {
            // 2. استدعاء الميثود من السيرفس المشترك
            $invoices = $this->invoiceService->getRoomsInvoices($roomIds);

            return response()->json([
                'success' => true,
                'data' => $invoices
            ], 200);
        } catch (\Exception $e) {
            return ApiResponse::error("حدث خطأ أثناء جلب فواتير الغرف: " . $e->getMessage(), 500);
        }
    }

    public function getDoctorInvoices(int $doctorId)
    {
        if (!$doctorId) {
            return ApiResponse::error("معرف الطبيب (doctor_id) مطلوب.", 400);
        }

        try {
            $invoices = $this->invoiceService->getDoctorInvoices($doctorId);

            return ApiResponse::success($invoices, 'تم جلب فواتير الطبيب بنجاح.');
        } catch (\Exception $e) {
            return ApiResponse::error("حدث خطأ أثناء جلب فواتير الطبيب: " . $e->getMessage(), 500);
        }
    }

    public function getInvoiceWithPayments($invoice_id)
    {
        if (!$invoice_id) {
            return ApiResponse::error("معرف الفاتورة (invoice_id) مطلوب.", 400);
        }

        try {
            $invoiceDetails = $this->invoiceService->getInvoiceWithPayments($invoice_id);

            return ApiResponse::success($invoiceDetails, 'تم جلب تفاصيل الفاتورة والمدفوعات المرتبطة بها بنجاح.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error("الفاتورة المطلوبة غير موجودة.", 404);
        } catch (\Exception $e) {
            return ApiResponse::error("حدث خطأ أثناء جلب تفاصيل الفاتورة: " . $e->getMessage(), 500);
        }
    }


    public function getAllInvoicesFiltered(Request $request)
    {
        $validated = $request->validate([
            'status'    => ['nullable', 'string', Rule::in(['paid', 'pending', 'refunded', 'partially_paid'])], // الحالات المدعومة عندك
            'date_from' => 'nullable|date|date_format:Y-m-d', // صيغة تاريخ القياسية YYYY-MM-DD
            'date_to'   => 'nullable|date|date_format:Y-m-d|after_or_equal:date_from', // تضمن إن النهاية بعد أو تساوي البداية
            'per_page'  => 'nullable|integer|min:1|max:100',
        ], [
            'status.in'          => 'الحالة المرسلة غير معرّفة بالنظام.',
            'date_from.date_format' => 'صيغة تاريخ البدء يجب أن تكون YYYY-MM-DD.',
            'date_to.date_format'   => 'صيغة تاريخ الانتهاء يجب أن تكون YYYY-MM-DD.',
            'date_to.after_or_equal' => 'تاريخ الانتهاء لا يمكن أن يكون قبل تاريخ البدء.',
        ]);
        $filters = [
            'status'    => $request->input('status'),     // مثال: paid, refunded, partially_paid
            'date_from' => $request->input('date_from'),  // مثال: 2026-01-01
            'date_to'   => $request->input('date_to'),    // مثال: 2026-06-28
        ];

        // تحديد عدد العناصر في الصفحة (اختياري من الفرونت إند مع قيمة افتراضية 15)
        $perPage = $request->input('per_page', 15);

        try {
            $paginatedData = $this->invoiceService->getAllInvoicesFiltered($filters, $perPage);

            return ApiResponse::success($paginatedData, 'تم جلب الفواتير المفلترة بنجاح.');
        } catch (\Exception $e) {
            return ApiResponse::error("حدث خطأ أثناء جلب الفواتير المفلترة: " . $e->getMessage(), 500);
        }
    }
}
