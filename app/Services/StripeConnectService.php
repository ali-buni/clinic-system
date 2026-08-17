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
    private const STRIPE_V2_VERSION = '2026-07-29.preview';

    private StripeClient $client;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->client = new StripeClient(['api_key' => config('services.stripe.secret'), 'stripe_version' => self::STRIPE_V2_VERSION]);
    }

    public function createConnectedAccount(Doctor $doctor): string
    {
        try {
            $account = $this->client->v2->core->accounts->create([
                'contact_email' => $doctor->user->email,
                'metadata' => [
                    'doctor_id' => (string) $doctor->id,
                    'user_id' => (string) $doctor->user_id,
                ],
                'configuration' => [
                    'recipient' => [
                        'capabilities' => [
                            'stripe_balance' => [
                                'stripe_transfers' => [
                                    'requested' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ], ['stripe_version' => self::STRIPE_V2_VERSION]);

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
            throw new \RuntimeException('Failed to create Stripe connected account: ' . $e->getMessage());
        }
    }

    public function generateAccountLink(string $accountId): string
    {
        try {
            $accountLink = $this->client->v2->core->accountLinks->create([
                'account' => $accountId,
                'use_case' => [
                    'type' => 'account_onboarding',
                    'configurations' => ['recipient'],
                    'refresh_url' => url('/doctor/withdrawals/stripe/refresh'),
                    'return_url' => url('/doctor/withdrawals/stripe/return'),
                ],
            ], ['stripe_version' => self::STRIPE_V2_VERSION]);

            return $accountLink->url;
        } catch (ApiErrorException $e) {
            Log::channel('structured')->error('Failed to generate account link', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Failed to generate Stripe onboarding link: ' . $e->getMessage());
        }
    }

    public function ensureConnectedAccountAndGetOnboardingUrl(Doctor $doctor)
    {
        $accountId = $doctor->stripe_connected_account_id;

        if (! $accountId) {
            $accountId = $this->createConnectedAccount($doctor);
        }

        $accountUrl = $this->generateAccountLink($accountId);

        Log::channel('structured')->info('Stripe onboarding URL generated, sending notification', [
            'doctor_id' => $doctor->id,
            'stripe_account_id' => $accountId,
        ]);

        $doctor->user->notify(new SendStripeAccountLink($accountUrl, $doctor));

        return $accountUrl;
    }

    public function createTransfer(Doctor $doctor, float $amount, ?string $idempotencyKey = null): string
    {
        $accountId = $doctor->stripe_connected_account_id;

        if (! $accountId) {
            throw new \RuntimeException('Doctor does not have a Stripe connected account.');
        }

        try {
            $params = [
                'amount' => (int) round($amount * 100),
                'currency' => config('services.stripe.currency', 'usd'),
                'destination' => $accountId,
                'metadata' => [
                    'doctor_id' => $doctor->id,
                ],
            ];

            $options = [];
            if ($idempotencyKey !== null) {
                $options['idempotency_key'] = $idempotencyKey;
            }

            $transfer = $this->client->transfers->create($params, $options);

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
            throw new \RuntimeException('Stripe transfer failed: ' . $e->getMessage());
        }
    }

    public function getAccountStatus(string $accountId): array
    {
        try {
            $account = $this->client->v2->core->accounts->retrieve($accountId, [
                'include' => ['configuration.recipient'],
            ], ['stripe_version' => self::STRIPE_V2_VERSION]);

            $recipientConfig = $account->configuration->recipient ?? null;
            $transfersStatus = $recipientConfig?->capabilities?->stripe_balance?->stripe_transfers?->status ?? null;

            return [
                'id' => $account->id,
                'closed' => $account->closed ?? false,
                'applied_configurations' => $account->applied_configurations ?? [],
                'recipient_configured' => $recipientConfig?->applied ?? false,
                'transfers_enabled' => $transfersStatus === 'active',
                'requirements' => $account->requirements ?? null,
            ];
        } catch (ApiErrorException $e) {
            Log::channel('structured')->error('Failed to retrieve Stripe account status', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Failed to retrieve account status: ' . $e->getMessage());
        }
    }
}
