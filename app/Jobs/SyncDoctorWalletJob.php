<?php

namespace App\Jobs;

use App\Models\Doctor;
use App\Services\DoctorEarningsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class SyncDoctorWalletJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public readonly int $doctorId,
    ) {
        $this->afterCommit = true;
    }

    public function middleware(): array
    {
        return [new WithoutOverlapping("doctor-wallet-{$this->doctorId}")];
    }

    public function handle(DoctorEarningsService $earningsService): void
    {
        $doctor = Doctor::findOrFail($this->doctorId);
        $wallet = $earningsService->syncWalletBalance($doctor);

        Log::channel('structured')->info('Doctor wallet synced via job', [
            'doctor_id' => $this->doctorId,
            'balance' => $wallet->balance,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('structured')->error('SyncDoctorWalletJob failed', [
            'doctor_id' => $this->doctorId,
            'error' => $exception->getMessage(),
        ]);
    }
}
