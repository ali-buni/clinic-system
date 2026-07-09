<?php

namespace App\Listeners;

use App\Events\SendMsgEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SendMsgListener implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    private string $url;
    private string $token;

    public function __construct()
    {
        $this->url = config('services.traccar.url');
        $this->token = config('services.traccar.api');
    }

    public function handle(SendMsgEvent $event): void
    {
        if (empty($this->url)) {
            return;
        }

        $phone = $event->phone;
        $msg = $event->msg;

        if (empty($phone) || empty($msg)) {
            Log::channel('structured')->warning('[SMS] Missing phone or message', [
                'phone' => $phone,
                'msg' => $msg,
            ]);
            return;
        }

        $response = Http::asForm()->post($this->url, [
            'token' => $this->token,
            'to' => $phone,
            'body' => $msg
        ]);

        Log::channel('structured')->info('[SMS] Response', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('SMS failed: ' . $response->reason());
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('structured')->error('[SMS] Job failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
