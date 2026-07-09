<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadAndStoreImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public readonly int $userId,
        public readonly string $imageUrl,
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);
        if (! $user) {
            return;
        }

        $name = now()->format('Ymd_His') . '_' . uniqid() . '.jpg';
        $contents = Http::timeout(15)->get($this->imageUrl)->body();
        Storage::disk('public')->put('profile_images/' . $name, $contents);

        $user->update(['profile_image' => 'profile_images/' . $name]);

        Log::channel('structured')->info('Image downloaded and stored', [
            'user_id' => $this->userId,
            'path' => 'profile_images/' . $name,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('structured')->error('DownloadAndStoreImageJob failed', [
            'user_id' => $this->userId,
            'url' => $this->imageUrl,
            'error' => $exception->getMessage(),
        ]);
    }
}
