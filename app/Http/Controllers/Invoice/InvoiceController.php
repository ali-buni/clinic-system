<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\CreateInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Requests\Invoice\GetInvoicesRequest;
use App\Http\Resources\Invoice\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    public function store(CreateInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->invoiceService->createInvoice($request->validated());

        return ApiResponse::success(
            new InvoiceResource($invoice),
            'تم إنشاء الفاتورة وحساب قيمتها الإجمالية تلقائياً.',
            201
        );
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $data = array_merge($request->validated(), ['invoice_id' => $invoice->id]);
        $updated = $this->invoiceService->updateInvoice($data);

        return ApiResponse::success(
            new InvoiceResource($updated),
            'تم تعديل الفاتورة وإعادة احتساب قيمتها الإجمالية بنجاح.'
        );
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->invoiceService->deleteInvoice($invoice->id);

        return ApiResponse::success(null, 'تم حذف الفاتورة بنجاح.');
    }

    public function index(GetInvoicesRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $perPage = $filters['per_page'] ?? 15;
        unset($filters['per_page']);

        $paginated = $this->invoiceService->getAllInvoicesFiltered($filters, $perPage);

        return ApiResponse::pagination(
            $paginated,
            'تم جلب الفواتير المفلترة بنجاح.',
            $paginated->items()
        );
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load('payments');

        $details = $this->invoiceService->getInvoiceWithPayments($invoice->id);

        return ApiResponse::success($details, 'تم جلب تفاصيل الفاتورة والمدفوعات المرتبطة بها بنجاح.');
    }

    public function patientInvoices(int $patientId): JsonResponse
    {
        $invoices = $this->invoiceService->getPatientInvoices($patientId);

        return ApiResponse::success($invoices, 'تم جلب فواتير المريض بنجاح.');
    }

    public function roomsInvoices(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_ids'   => 'required|array|min:1',
            'room_ids.*' => 'required|integer|exists:rooms,id',
        ]);

        $invoices = $this->invoiceService->getRoomsInvoices($validated['room_ids']);

        return ApiResponse::success($invoices, 'تم جلب فواتير الغرف بنجاح.');
    }

    public function doctorInvoices(int $doctorId): JsonResponse
    {
        $invoices = $this->invoiceService->getDoctorInvoices($doctorId);

        return ApiResponse::success($invoices, 'تم جلب فواتير الطبيب بنجاح.');
    }
}
