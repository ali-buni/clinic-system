<?php

namespace App\Listeners;

use App\Events\SendMsgEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendMsgListener
{
    use InteractsWithQueue, Queueable, SerializesModels;

    private ?string $url;
    private ?string $token;

    public function __construct()
    {
        $this->url = config('services.traccar.url', 'https://fallback-url.com');
        $this->token = config('services.traccar.api', '');
    }

    /**
     * Handle the event.
     */
    public function handle(SendMsgEvent $event): void
    {
        if (!$this->url || !$this->token) {
        Log::error('[SMS] Traccar URL or Token is missing in configuration.');
        return;
    }
        $phone = $event->phone;
        // $phone = '+963939688965';

        $msg = $event->msg;

        $res = Http::withHeaders([
            'Authorization' => $this->token,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ])->post($this->url, [
            "to" => $phone,
            "message" => $msg,
        ]);

        Log::info('[SMS] Response', [
            'status' => $res->status(),
            'body'   => $res->body(),
        ]);
    }
}
