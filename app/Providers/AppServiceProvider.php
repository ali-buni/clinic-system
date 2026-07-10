<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorWallet;
use App\Models\DoctorWithdrawal;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Patient_record;
use App\Models\PatientInfo;
use App\Models\Payment;
use App\Models\Payment_method;
use App\Models\Refund;
use App\Models\User;
use App\Models\Verification_code;
use App\Observers\AppointmentObserver;
use App\Observers\DoctorObserver;
use App\Observers\DoctorWalletObserver;
use App\Observers\DoctorWithdrawalObserver;
use App\Observers\InvoiceObserver;
use App\Observers\ItemObserver;
use App\Observers\PatientInfoObserver;
use App\Observers\PatientRecordObserver;
use App\Observers\PaymentMethodObserver;
use App\Observers\PaymentObserver;
use App\Observers\RefundObserver;
use App\Observers\UserObserver;
use App\Observers\VerificationCodeObserver;
use App\Services\Ai\Contracts\AiProviderInterface;
use App\Services\Ai\MultiProviderRouter;
use App\Services\Ai\OllamaClient;
use App\Services\Ai\OpenAiCompatibleProvider;
use App\Services\Analytics\SettingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingService::class, function ($app) {
            return new SettingService($app->make('cache.store'));
        });

        $this->app->bind(AiProviderInterface::class, function () {
            $providerNames = explode(',', config('ai.default', 'ollama'));

            if (count($providerNames) > 1) {
                return $this->buildMultiProvider($providerNames);
            }

            return $this->buildSingleProvider(trim($providerNames[0]));
        });
    }

    private function buildSingleProvider(string $name): AiProviderInterface
    {
        $config = config("ai.providers.{$name}");
        if (! $config) {
            throw new \RuntimeException("AI provider '{$name}' is not configured in config/ai.php");
        }

        return match ($config['driver'] ?? 'ollama') {
            'openai' => new OpenAiCompatibleProvider($name),
            default => new OllamaClient,
        };
    }

    private function buildMultiProvider(array $providerNames): MultiProviderRouter
    {
        $providers = [];
        $names = [];

        foreach ($providerNames as $name) {
            $name = trim($name);
            try {
                $providers[] = $this->buildSingleProvider($name);
                $names[] = $name;
            } catch (\RuntimeException $e) {
                // skip misconfigured providers in chain
                continue;
            }
        }

        if (empty($providers)) {
            throw new \RuntimeException('No AI providers could be initialized from the chain: '.implode(',', $providerNames));
        }

        return new MultiProviderRouter($providers, $names);
    }

    public function boot(): void
    {
        Appointment::observe(AppointmentObserver::class);
        Patient_record::observe(PatientRecordObserver::class);
        PatientInfo::observe(PatientInfoObserver::class);
        Doctor::observe(DoctorObserver::class);
        User::observe(UserObserver::class);
        Invoice::observe(InvoiceObserver::class);
        Payment::observe(PaymentObserver::class);
        Payment_method::observe(PaymentMethodObserver::class);
        Item::observe(ItemObserver::class);
        Refund::observe(RefundObserver::class);
        DoctorWallet::observe(DoctorWalletObserver::class);
        DoctorWithdrawal::observe(DoctorWithdrawalObserver::class);
        Verification_code::observe(VerificationCodeObserver::class);
    }
}
