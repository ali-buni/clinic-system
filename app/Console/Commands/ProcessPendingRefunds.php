<?php

namespace App\Console\Commands;

use App\Jobs\ProcessStripeRefundJob;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessPendingRefunds extends Command
{
    protected $signature = 'app:process-pending-refunds';

    protected $description = 'Retry failed or pending Stripe refunds';

    public function handle(): int
    {
        $payments = Payment::whereNotNull('paid_at')
            ->where('stripe_payment_intent_id', '!=', null)
            ->where('refunded_amount', '<', DB::raw('amount'))
            ->get();

        $count = 0;
        foreach ($payments as $payment) {
            $refundable = $payment->getRefundableAmount();
            if ($refundable > 0) {
                ProcessStripeRefundJob::dispatchSync(
                    $payment->id,
                    $refundable,
                    'Auto-refund: pending refund retry',
                );
                $count++;
            }
        }

        $this->info("Dispatched {$count} pending refund jobs.");

        return self::SUCCESS;
    }
}
