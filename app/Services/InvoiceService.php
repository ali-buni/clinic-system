<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Item;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function createInvoice(array $data): Invoice
    {
        $appointment = Appointment::find($data['appointment_id']);
        if (! $appointment || $appointment->status !== 'completed') {
            throw new ModelNotFoundException('no completed appointment found.', 404);
        }

        $this->assertItemsBelongToClinic($data['invoice_items'], $appointment->clinic_id);

        return DB::transaction(function () use ($data, $appointment) {
            $invoiceNumber = $this->generateInvoiceNumber();

            $totalCost = collect($data['invoice_items'])
                ->sum(fn(array $item) => $item['price'] * $item['quantity']);

            $invoice = Invoice::create([
                'clinic_id' => $appointment->clinic_id,
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
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

    public function createBookingInvoice(Appointment $appointment): Invoice
    {
        return DB::transaction(function () use ($appointment) {
            $consultationFee = (float) $appointment->doctor->consultation_fee;
            $slotsCount = (int) $appointment->type->types;
            $totalCost = $consultationFee * $slotsCount;

            $invoiceNumber = $this->generateInvoiceNumber();

            $consultationItem = Item::where('item_name', 'Consultation Fee')
                ->where(fn($q) => $q->where('clinic_id', $appointment->clinic_id)
                    ->orWhereNull('clinic_id'))
                ->firstOrFail();

            $invoice = Invoice::create([
                'clinic_id' => $appointment->clinic_id,
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'invoice_number' => $invoiceNumber,
                'total_cost' => $totalCost,
                'description' => 'Booking fee',
                'status' => InvoiceStatus::Draft->value,
            ]);

            $invoice->items()->attach($consultationItem->id, [
                'price' => $consultationFee,
                'quantity' => $slotsCount,
            ]);

            return $invoice->fresh();
        });
    }

    public function updateBookingInvoice(Invoice $invoice, Appointment $appointment): void
    {
        DB::transaction(function () use ($invoice, $appointment) {
            $consultationFee = (float) $appointment->doctor->consultation_fee;
            $slotsCount = (int) $appointment->type->types;
            $newTotalCost = $consultationFee * $slotsCount;
            $oldTotalCost = (float) $invoice->total_cost;

            $invoice->update(['total_cost' => $newTotalCost]);

            $consultationItem = Item::where('item_name', 'Consultation Fee')
                ->where(fn($q) => $q->where('clinic_id', $appointment->clinic_id)
                    ->orWhereNull('clinic_id'))
                ->first();

            if ($consultationItem) {
                $invoice->items()->updateExistingPivot($consultationItem->id, [
                    'price' => $consultationFee,
                    'quantity' => $slotsCount,
                ]);
            }

            $pendingPayment = $invoice->payments()
                ->whereNull('paid_at')
                ->first();

            if ($pendingPayment) {
                $pendingPayment->update(['amount' => $newTotalCost]);
            }

            if ($newTotalCost < $oldTotalCost) {
                $this->paymentService->refundOverpaidStripePayments($invoice, $newTotalCost);
            }

            $this->paymentService->syncInvoiceStatus($invoice);
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
                $this->assertItemsBelongToClinic($data['updated_items'], $invoice->clinic_id);

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
            ->paginate($perPage, page: $filters['page'] ?? 1);

        $invoices->each(function ($invoice) {
            $invoice->total_paid = $invoice->completedPayments->sum('amount');
        });

        return $invoices;
    }

    public function getPatientInvoices(int $patientId): Collection
    {
        return Invoice::where('patient_id', $patientId)
            ->with(['completedPayments'])
            ->orderBy('created_at', 'desc')
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
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(function ($invoice) {
                $invoice->total_paid = $invoice->completedPayments->sum('amount');
            });
    }

    private function assertItemsBelongToClinic(array $items, int $clinicId): void
    {
        $foreignItemExists = Item::whereIn('id', collect($items)->pluck('item_id')->all())
            ->whereNotNull('clinic_id')
            ->where('clinic_id', '!=', $clinicId)
            ->exists();

        if ($foreignItemExists) {
            throw new \InvalidArgumentException('Invoice item does not belong to the appointment clinic.');
        }
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
