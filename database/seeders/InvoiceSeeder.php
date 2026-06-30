<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Payment_method;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $statusMaps = require __DIR__ . '/../data/status_maps.php';
        $statusMap = $statusMaps['invoice_map'];

        $clinic = Clinic::first();
        if (!$clinic) return;

        $appointments = Appointment::all();
        $paymentMethods = Payment_method::all();

        foreach ($appointments as $appointment) {
            $invoiceStatus = $statusMap[$appointment->status] ?? 'draft';
            $totalCost = $appointment->doctor->consultation_fee ?? fake()->randomFloat(2, 80, 500);

            $invoice = Invoice::create([
                'clinic_id' => $clinic->id,
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'status' => $invoiceStatus,
                'total_cost' => $totalCost,
                'description' => 'Appointment invoice - ' . $appointment->status,
            ]);

            $itemIds = Item::inRandomOrder()->take(fake()->numberBetween(1, 3))->pluck('id');
            foreach ($itemIds as $itemId) {
                $invoice->items()->attach($itemId, [
                    'quantity' => fake()->numberBetween(1, 3),
                    'price' => fake()->randomFloat(2, 10, 200),
                ]);
            }

            if ($invoiceStatus === 'paid') {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'payment_method_id' => $paymentMethods->random()->id,
                    'amount' => $totalCost,
                    'paid_at' => $appointment->start_time,
                ]);
            }
        }
    }
}
