<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $invoiceNumber = $this->generateInvoiceNumber();

            $totalCost = collect($data['invoice_items'])
                ->sum(fn(array $item) => $item['price'] * $item['quantity']);

            $invoice = Invoice::create([
                'clinic_id' => $data['clinic_id'],
                'patient_id' => $data['patient_id'],
                'appointment_id' => $data['appointment_id'],
                'invoice_number' => $invoiceNumber,
                'total_cost' => $totalCost,
                'description' => $data['description'] ?? null,
                'status' => InvoiceStatus::Draft->value,
            ]);

            $items = collect($data['invoice_items'])->mapWithKeys(fn($item) => [
                $item['item_id'] => [
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                ],
            ]);

            $invoice->items()->attach($items->all());

            return $invoice->fresh();
        });
    }

    public function updateInvoice(int $invoiceId, array $data): Invoice
    {
        return DB::transaction(function () use ($invoiceId, $data) {
            $invoice = Invoice::lockForUpdate()->findOrFail($invoiceId);

            if (array_key_exists('description', $data)) {
                $invoice->description = $data['description'];
            }

            if (! empty($data['updated_items'])) {
                $totalCost = collect($data['updated_items'])
                    ->sum(fn(array $item) => $item['price'] * $item['quantity']);

                $syncData = collect($data['updated_items'])->mapWithKeys(fn($item) => [
                    $item['item_id'] => [
                        'price' => $item['price'],
                        'quantity' => $item['quantity'],
                    ],
                ]);

                $invoice->total_cost = $totalCost;
                $invoice->items()->sync($syncData->all());
            }

            $invoice->save();

            $this->paymentService->refundOverpaidStripePayments($invoice, (float) $invoice->total_cost);

            return $invoice->fresh();
        });
    }

    public function deleteInvoice(int $invoiceId): void
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $invoice->delete();
    }

    public function getAllInvoicesFiltered(array $filters, int $perPage = 15)
    {
        $invoices = Invoice::query()
            ->with(['completedPayments'])
            ->when($filters['status'] ?? null, fn($q, $s) => $q->where('status', $s))
            ->when($filters['date_from'] ?? null, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->where('clinic_id', $filters['clinic_id'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(function ($invoice) {
                $invoice->total_paid = $invoice->completedPayments->sum('amount');
            });

        return $invoices->paginate($perPage, page: $filters['page'] ?? 1);
    }

    public function getPatientInvoices(int $patientId): Collection
    {
        return Invoice::where('patient_id', $patientId)
            ->with(['completedPayments'])
            ->get()
            ->each(function ($invoice) {
                $invoice->total_paid = $invoice->completedPayments->sum('amount');
            });
    }

    public function getRoomsInvoices(array $roomIds): Collection
    {
        return Invoice::whereHas('appointment', fn($q) => $q->whereIn('room_id', $roomIds))
            ->with(['completedPayments'])
            ->get()
            ->each(function ($invoice) {
                $invoice->total_paid = $invoice->completedPayments->sum('amount');
            });
    }

    public function getDoctorInvoices(int $doctorId): Collection
    {
        return Invoice::whereHas('appointment', fn($q) => $q->where('doctor_id', $doctorId))
            ->with(['completedPayments'])
            ->get()
            ->each(function ($invoice) {
                $invoice->total_paid = $invoice->completedPayments->sum('amount');
            });
    }

    private function generateInvoiceNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = 'INV-' . $year . '-';

            DB::statement('
            INSERT INTO invoice_sequences (year, last_number, created_at, updated_at)
            VALUES (?, 1, NOW(), NOW())
            ON DUPLICATE KEY UPDATE last_number = LAST_INSERT_ID(last_number + 1)
        ', [$year]);

            $number = DB::getPdo()->lastInsertId();

            return $prefix . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
        });
    }
}
