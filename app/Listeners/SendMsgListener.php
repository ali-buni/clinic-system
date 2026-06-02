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

    private string $url;
    private string $token;

    public function __construct()
    {
        $this->url = config('services.traccar.url');
        $this->token = config('services.traccar.api');
    }

    /**
     * Handle the event.
     */
    public function handle(SendMsgEvent $event): void
    {
        // Logic to send message
        $phone = $event->phone;
        $msg = $event->msg;

        try {
            $response = Http::asForm()->post($this->url, [
                'token' => $this->token,
                'to' => $phone,
                'body' => $msg
            ]);

            Log::info('[SMS] Response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            if ($response->successful()) {
                echo $response->body();
            } else {
                echo 'Unexpected HTTP status: ' . $response->status() . ' ' .
                    $response->reason();
            }
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}
