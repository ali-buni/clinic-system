<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\CreateInvoiceRequest;
use App\Http\Requests\Invoice\GetInvoicesRequest;
use App\Http\Requests\Invoice\GetRoomsInvoicesRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Resources\Invoice\InvoiceResource;
use App\Models\Invoice;
use App\Services\ApiResponse;
use App\Services\InvoiceService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    public function index(GetInvoicesRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = (int) ($filters['per_page'] ?? 15);
        unset($filters['per_page']);

        $paginated = $this->invoiceService->getAllInvoicesFiltered($filters, $perPage);

        return ApiResponse::pagination(
            $paginated,
            'success',
            InvoiceResource::collection($paginated->items())
        );
    }

    public function store(CreateInvoiceRequest $request): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->createInvoice($request->validated());

            return ApiResponse::success(
                new InvoiceResource($invoice),
                'success',
                201
            );
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    public function show(int $invoiceId): JsonResponse
    {
        try {
            $invoice = Invoice::with(['completedPayments.paymentMethod', 'items'])
                ->find($invoiceId);

            if (! $invoice) {
                return ApiResponse::error('the invoice not found', 404);
            }

            $invoice->total_paid = $invoice->completedPayments->sum('amount');

            return ApiResponse::success(
                new InvoiceResource($invoice),
                'success'
            );
        } catch (\Throwable $th) {
            return ApiResponse::error('Server Error');
        }
    }

    public function update(UpdateInvoiceRequest $request, int $invoiceId): JsonResponse
    {
        $updated = $this->invoiceService->updateInvoice($invoiceId, $request->validated());

        return ApiResponse::success(
            new InvoiceResource($updated),
            'success'
        );
    }

    public function destroy(int $invoiceId): JsonResponse
    {
        $this->invoiceService->deleteInvoice($invoiceId);

        return ApiResponse::success(null, 'success');
    }

    public function patientInvoices(int $patientId): JsonResponse
    {
        try {
            $invoices = $this->invoiceService->getPatientInvoices($patientId);
            if ($invoices->isEmpty()) {
                return ApiResponse::error('the invoice not found', 404);
            }

            return ApiResponse::success(
                InvoiceResource::collection($invoices),
                'success'
            );
        } catch (\Throwable $th) {
            return ApiResponse::error('Server Error');
        }
    }

    public function roomsInvoices(GetRoomsInvoicesRequest $request): JsonResponse
    {
        try {
            $invoices = $this->invoiceService->getRoomsInvoices($request->validated('room_ids'));
            if ($invoices->isEmpty()) {
                return ApiResponse::error('the invoice not found', 404);
            }

            return ApiResponse::success(
                InvoiceResource::collection($invoices),
                'success'
            );
        } catch (\Throwable $th) {
            return ApiResponse::error('Server Error');
        }
    }

    public function doctorInvoices(int $doctorId): JsonResponse
    {
        try {
            $invoices = $this->invoiceService->getDoctorInvoices($doctorId);
            if ($invoices->isEmpty()) {
                return ApiResponse::error('the invoice not found', 404);
            }

            return ApiResponse::success(
                InvoiceResource::collection($invoices),
                'success'
            );
        } catch (\Throwable $th) {
            return ApiResponse::error('Server Error');
        }
    }
}
