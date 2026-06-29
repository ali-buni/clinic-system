<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Patientinfo;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\models\Payment;
use App\Services\PaymentService;

class InvoiceService
{
    /**
     * إنشاء فاتورة جديدة وحساب قيمتها تلقائياً
     */
    public function createInvoice(array $data): Invoice
    {
        // 1. توليد رقم فاتورة فريد آلياً (مثلاً: INV-وبعدها رقم عشوائي أو Timestamp)
        $invoiceNumber = 'INV-' . strtoupper(Str::random(4)) . time();

        // 2. حساب إجمالي القيمة المادية برمجياً (السعر × الكمية) لكل البنود مسبقاً
        $totalCost = 0;
        foreach ($data['invoice_items'] as $item) {
            $totalCost += $item['price'] * $item['quantity'];
        }

        // 3. إنشاء سطر الفاتورة الرئيسي
        $invoice = Invoice::create([
            'clinic_id'      => $data['clinic_id'],
            'patient_id'     => $data['patient_id'],
            'appointment_id' => $data['appointment_id'],
            'invoice_number' => $invoiceNumber,
            'total_cost'     => $totalCost,
            'description'    => $data['description'] ?? null,
        ]);

        // 4. ربط العناصر بالفاتورة في جدول الـ Pivot (invoice_items) وتخزين بيانات الـ Pivot
        foreach ($data['invoice_items'] as $item) {
            $invoice->items()->attach($item['item_id'], [
                'price'    => $item['price'],
                'quantity' => $item['quantity'],
            ]);
        }

        $invoice->save(); // حفظ التغييرات على الفاتورة بعد إعادة تعيين القيمة الإجمالية

        return $invoice;
    }

    /**
     * تعديل فاتورة وإعادة احتساب القيمة الإجمالية تلقائياً بناءً على العناصر الجديدة
     */
    public function updateInvoice(array $data): Invoice
    {
        // استخدام findOrFail الحازمة لفرز 404 تلقائياً إذا المعرف مو موجود
        $invoice = Invoice::findOrFail($data['invoice_id']);

        // تحديث الوصف إذا تم تمريره
        if (array_key_exists('description', $data)) {
            $invoice->description = $data['description'];
        }

        // إذا تم إرسال مصفوفة العناصر المحدثة، بنعيد الحسبة كاملة
        if (!empty($data['updated_items'])) {
            $totalCost = 0;
            $syncData = [];

            foreach ($data['updated_items'] as $item) {
                $totalCost += $item['price'] * $item['quantity'];

                $syncData[$item['item_id']] = [
                    'price'     => $item['price'],
                    'quantity'  => $item['quantity'],
                ];
            }

            // إسناد المجموع المالي الجديد للفاتورة
            $invoice->total_cost = $totalCost;

            // تحديث جدول الـ Pivot (حذف القديم وتنزيل الجديد فوراً)
            $invoice->items()->sync($syncData);
        }

        $invoice->save();

        return $invoice;
    }

    public function deleteInvoice(int $invoiceId): void
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $invoice->delete();
    }



    public function createPaymentLink(int $invoiceId): string
    {
        $invoice = Invoice::findOrFail($invoiceId);
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'فاتورة طبية رقم: ' . $invoice->invoice_number,
                    ],
                    'unit_amount' => $invoice->total_cost * 100, // سترايب بيتعامل بالسنت
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => 'https://yourdomain.com/payment-success?invoice_id=' . $invoice->id,
            'cancel_url' => 'https://yourdomain.com/payment-failed',
            'metadata' => [
                'invoice_id' => $invoice->id // كرمال نلقطها بالـ Webhook بعدين
            ]
        ]);

        return $session->url;
    }



    public function getPatientInvoices(int $patientId)
    {
        // 1. جلب المريض وفواتيره
        $patient = PatientInfo::findOrFail($patientId);
        $invoices = $patient->invoices()->get(['id', 'patient_id', 'total_cost']);

        // 2. استخدام التابع تبعك لحساب الحسبة لكل فاتورة بدقة
        return $invoices->map(function ($invoice) {

            $remaining = $invoice->getRemainingBalance();
            $paid = $invoice->total_cost - $remaining;

            return [
                'id' => $invoice->id,
                'total_cost' => $invoice->total_cost,
                'paid_amount' => number_format($paid, 2, '.', ''),
                'remaining_amount' => number_format($remaining, 2, '.', ''),
            ];
        });
    }

    public function getRoomsInvoices(array $roomIds)
    {
        // 1. جلب الفواتير التي تنتمي مواعيدها للغرف المطلوبة
        // بنستخدم whereHas لحتى نعمل فحص على جدول المواعيد المرتبط بالفاتورة
        $invoices = Invoice::whereHas('appointment', function ($query) use ($roomIds) {
            $query->whereIn('room_id', $roomIds); // بيفترض وجود عمود room_id بجدول الـ appointments
        })->get(['id', 'total_cost', 'status', 'appointment_id']);

        // 2. تنسيق البيانات وعمل الحسبة المالية (المسدد والمتبقي) لكل فاتورة بنفس المنطق المشترك
        return $invoices->map(function ($invoice) {

            // استدعاء التابع المشترك لحساب المتبقي من موديل الـ Invoice
            $remaining = $invoice->getRemainingBalance();
            $paid = $invoice->total_cost - $remaining;

            return [
                'id' => $invoice->id,
                'total_cost' => $invoice->total_cost,
                'paid_amount' => number_format($paid, 2, '.', ''),
                'remaining_amount' => number_format($remaining, 2, '.', ''),
                'status' => $invoice->status,
            ];
        });
    }


    public function getDoctorInvoices(int $doctorId)
    {
        $invoices = Invoice::whereHas('appointment', function ($query) use ($doctorId) {
            $query->where('doctor_id', $doctorId); // بيفترض وجود عمود doctor_id في جدول الـ appointments
        })->get(['id', 'total_cost', 'status', 'appointment_id']);

        // 2. حساب المسدد والمتبقي لتسهيل الحسابات المالية للطبيب
        return $invoices->map(function ($invoice) {

            $remaining = $invoice->getRemainingBalance();
            $paid = $invoice->total_cost - $remaining;

            return [
                'id' => $invoice->id,
                'total_cost' => $invoice->total_cost,
                'paid_amount' => number_format($paid, 2, '.', ''),
                'remaining_amount' => number_format($remaining, 2, '.', ''),
                'status' => $invoice->status,
            ];
        });
    }
    public function getInvoiceWithPayments(int $invoiceId)
    {
        $invoice = Invoice::with('payments')->findOrFail($invoiceId);

        // حساب المبالغ المسددة والمتبقية
        $remaining = $invoice->getRemainingBalance();
        $paid = $invoice->total_cost - $remaining;

        return [
            'id' => $invoice->id,
            'total_cost' => $invoice->total_cost,
            'paid_amount' => number_format($paid, 2, '.', ''),
            'remaining_amount' => number_format($remaining, 2, '.', ''),
            'status' => $invoice->status,
            'payments' => $invoice->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'paid_at' => $payment->paid_at,
                    'created_at' => $payment->created_at,
                ];
            }),
        ];
    }

    public function getAllInvoicesFiltered(array $filters, int $perPage = 15)
    {
        // 1. بناء الكويري الأساسي للفواتير
        $query = Invoice::query();

        // 2. تطبيق فلتر الحالة (status) إذا تم تمريره
        $query->when(!empty($filters['status']), function ($q) use ($filters) {
            return $q->where('status', $filters['status']);
        });

        // 3. تطبيق فلتر تاريخ البدء (date_from) إذا تم تمريره
        $query->when(!empty($filters['date_from']), function ($q) use ($filters) {
            return $q->whereDate('created_at', '>=', $filters['date_from']);
        });

        // 4. تطبيق فلتر تاريخ الانتهاء (date_to) إذا تم تمريره
        $query->when(!empty($filters['date_to']), function ($q) use ($filters) {
            return $q->whereDate('created_at', '<=', $filters['date_to']);
        });

        // 5. ترتيب الفواتير من الأحدث للأقدم مع التقسيم لصفحات (Pagination)
        $paginatedInvoices = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // 6. تنسيق البيانات وحساب المسدد والمتبقي لكل فاتورة مع الحفاظ على هيكلية الـ Paginator
        $paginatedInvoices->getCollection()->transform(function ($invoice) {
            $remaining = $invoice->getRemainingBalance();
            $paid = $invoice->total_cost - $remaining;

            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status,
                'total_cost' => $invoice->total_cost,
                'paid_amount' => number_format($paid, 2, '.', ''),
                'remaining_amount' => number_format($remaining, 2, '.', ''),
                'created_at' => $invoice->created_at->toDateTimeString(),
            ];
        });

        return $paginatedInvoices;
    }
}
