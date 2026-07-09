<?php

namespace App\Services;

use App\Models\Doctor;
use App\Notifications\SendStripeAccountLink;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeConnectService
{
    private StripeClient $client;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->client = new StripeClient(config('services.stripe.secret'));
    }

    public function createConnectedAccount(Doctor $doctor): string
    {
        try {
            $account = $this->client->accounts->create([
                'type' => 'express',
                'email' => $doctor->user->email,
                'metadata' => [
                    'doctor_id' => $doctor->id,
                    'user_id' => $doctor->user_id,
                ],
                'capabilities' => [
                    'transfers' => ['requested' => true],
                ],
            ]);

            $doctor->update(['stripe_connected_account_id' => $account->id]);

            Log::channel('structured')->info('Stripe connected account created', [
                'doctor_id' => $doctor->id,
                'stripe_account_id' => $account->id,
            ]);

            return $account->id;
        } catch (ApiErrorException $e) {
            Log::channel('structured')->error('Failed to create Stripe connected account', [
                'doctor_id' => $doctor->id,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Failed to create Stripe connected account: '.$e->getMessage());
        }
    }

    public function generateAccountLink(string $accountId): string
    {
        try {
            $accountLink = $this->client->accountLinks->create([
                'account' => $accountId,
                'refresh_url' => url('/doctor/withdrawals/stripe/refresh'),
                'return_url' => url('/doctor/withdrawals/stripe/return'),
                'type' => 'account_onboarding',
            ]);

            return $accountLink->url;
        } catch (ApiErrorException $e) {
            Log::channel('structured')->error('Failed to generate account link', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Failed to generate Stripe onboarding link: '.$e->getMessage());
        }
    }

    public function ensureConnectedAccountAndGetOnboardingUrl(Doctor $doctor)
    {
        $accountId = $doctor->stripe_connected_account_id;

        if (! $accountId) {
            $accountId = $this->createConnectedAccount($doctor);
        }

        $accountUrl = $this->generateAccountLink($accountId);
        $doctor->user->notify(new SendStripeAccountLink($accountUrl, $doctor));
        return $accountUrl;
    }

    public function createTransfer(Doctor $doctor, float $amount): string
    {
        $accountId = $doctor->stripe_connected_account_id;

        if (! $accountId) {
            throw new \RuntimeException('Doctor does not have a Stripe connected account.');
        }

        try {
            $transfer = $this->client->transfers->create([
                'amount' => (int) round($amount * 100),
                'currency' => config('services.stripe.currency', 'usd'),
                'destination' => $accountId,
                'metadata' => [
                    'doctor_id' => $doctor->id,
                ],
            ]);

            Log::channel('structured')->info('Stripe transfer created', [
                'doctor_id' => $doctor->id,
                'amount' => $amount,
                'transfer_id' => $transfer->id,
                'destination' => $accountId,
            ]);

            return $transfer->id;
        } catch (ApiErrorException $e) {
            Log::channel('structured')->error('Stripe transfer failed', [
                'doctor_id' => $doctor->id,
                'amount' => $amount,
                'destination' => $accountId,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Stripe transfer failed: '.$e->getMessage());
        }
    }

    public function getAccountStatus(string $accountId): array
    {
        try {
            $account = $this->client->accounts->retrieve($accountId);

            return [
                'id' => $account->id,
                'charges_enabled' => $account->charges_enabled,
                'payouts_enabled' => $account->payouts_enabled,
                'details_submitted' => $account->details_submitted,
            ];
        } catch (ApiErrorException $e) {
            Log::channel('structured')->error('Failed to retrieve Stripe account status', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Failed to retrieve account status: '.$e->getMessage());
        }
    }
}
