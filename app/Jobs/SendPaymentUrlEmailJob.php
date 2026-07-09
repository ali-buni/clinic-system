<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentUrlEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public readonly string $recipientEmail,
        public readonly string $clinicTitle,
        public readonly string $paymentUrl,
        public readonly string $subject = 'Payment URL - Clinic System',
    ) {}

    public function handle(): void
    {
        Mail::raw(
            "Payment URL for clinic {$this->clinicTitle}: {$this->paymentUrl}",
            function ($message) {
                $message->to($this->recipientEmail)
                    ->subject($this->subject);
            }
        );

        Log::channel('structured')->info('Payment URL email sent', [
            'recipient' => $this->recipientEmail,
            'clinic' => $this->clinicTitle,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('structured')->error('SendPaymentUrlEmailJob failed', [
            'recipient' => $this->recipientEmail,
            'error' => $exception->getMessage(),
        ]);
    }
}
