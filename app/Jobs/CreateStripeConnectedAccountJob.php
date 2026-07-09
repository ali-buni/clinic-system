<?php

namespace App\Jobs;

use App\Models\Doctor;
use App\Services\StripeConnectService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateStripeConnectedAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 60, 120];

    public function __construct(
        public readonly int $doctorId,
    ) {}

    public function handle(StripeConnectService $stripeConnectService): void
    {
        $doctor = Doctor::findOrFail($this->doctorId);

        if ($doctor->stripe_connected_account_id) {
            Log::channel('structured')->info('Doctor already has Stripe account, skipping', [
                'doctor_id' => $doctor->id,
                'stripe_account_id' => $doctor->stripe_connected_account_id,
            ]);
            return;
        }

        $onboardingUrl = $stripeConnectService->ensureConnectedAccountAndGetOnboardingUrl($doctor);

        Log::channel('structured')->info('Stripe connected account created via job', [
            'doctor_id' => $doctor->id,
            'onboarding_url' => $onboardingUrl,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('structured')->error('CreateStripeConnectedAccountJob failed', [
            'doctor_id' => $this->doctorId,
            'error' => $exception->getMessage(),
        ]);
    }
}
