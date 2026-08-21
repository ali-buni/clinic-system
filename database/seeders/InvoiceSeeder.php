<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Payment_method;
use App\Models\Refund;
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
            if ($appointment->invoices()->exists()) continue;

            $invoiceStatus = $statusMap[$appointment->status] ?? 'draft';
            $consultationFee = $appointment->doctor->consultation_fee ?? fake()->randomFloat(2, 80, 500);

            $attachData = [];
            Item::inRandomOrder()
                ->take(fake()->numberBetween(1, 3))
                ->each(function ($item) use (&$attachData) {
                    $attachData[$item->id] = [
                        'quantity' => fake()->numberBetween(1, 3),
                        'price' => fake()->randomFloat(2, 10, 200),
                    ];
                });

            $itemsTotal = collect($attachData)->sum(
                fn(array $pivot) => $pivot['price'] * $pivot['quantity']
            );
            $totalCost = round($consultationFee + $itemsTotal, 2);

            $invoice = Invoice::create([
                'clinic_id' => $clinic->id,
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'status' => $invoiceStatus,
                'total_cost' => $totalCost,
                'description' => 'Appointment invoice - ' . $appointment->status,
                'created_at' => $appointment->start_time,
            ]);

            foreach ($attachData as $itemId => $pivot) {
                $invoice->items()->attach($itemId, $pivot);
            }

            if ($invoiceStatus === 'paid') {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'payment_method_id' => $paymentMethods->random()->id,
                    'amount' => $totalCost,
                    'paid_at' => $appointment->start_time,
                    'refunded_amount' => 0,
                ]);
            } elseif ($invoiceStatus === 'refunded') {
                $payment = Payment::firstOrCreate(
                    ['invoice_id' => $invoice->id],
                    [
                        'payment_method_id' => $paymentMethods->random()->id,
                        'amount' => $totalCost,
                        'paid_at' => $appointment->start_time,
                        'refunded_amount' => $totalCost,
                    ]
                );

                Refund::firstOrCreate(
                    ['idempotency_key' => 'seed-refund-' . $invoice->id],
                    [
                        'payment_id' => $payment->id,
                        'invoice_id' => $invoice->id,
                        'amount' => $totalCost,
                        'reason' => 'No-show refund',
                        'refunded_by' => $clinic->user_id,
                    ]
                );
            }
        }
    }
}
