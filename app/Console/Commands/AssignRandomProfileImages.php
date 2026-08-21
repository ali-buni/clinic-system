<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssignRandomProfileImages extends Command
{
    protected $signature = 'users:random-images
        {--all : Re-roll every user, replacing existing photos}
        {--source=images : Source folder relative to storage/app/public}';

    protected $description = 'Assign random profile images from a storage folder to users';

    private const DEFAULT_IMAGE = 'defaults/avatar.svg';

    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function handle(): int
    {
        $source = trim((string) $this->option('source'), '/\\');
        $disk = Storage::disk('public');

        if (!$disk->exists($source)) {
            $this->error("Source folder [storage/app/public/{$source}] does not exist.");
            return self::FAILURE;
        }

        $images = collect($disk->files($source))
            ->filter(fn(string $path) => in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::ALLOWED_EXTENSIONS))
            ->values();

        if ($images->isEmpty()) {
            $this->error("No images (jpg, jpeg, png, webp, gif) found in [storage/app/public/{$source}].");
            return self::FAILURE;
        }

        $users = DB::table('users')
            ->when(!$this->option('all'), fn($query) => $query->where(function ($query) {
                $query->whereNull('profile_image')->orWhere('profile_image', self::DEFAULT_IMAGE);
            }))
            ->orderBy('id')
            ->pluck('profile_image', 'id');

        if ($users->isEmpty()) {
            $this->info('No users need a profile image.');
            return self::SUCCESS;
        }

        $pool = $images->shuffle()->values();
        $cursor = 0;
        $updated = 0;

        foreach ($users as $userId => $currentImage) {
            if ($cursor >= $pool->count()) {
                $pool = $images->shuffle()->values();
                $cursor = 0;
            }

            if ($this->option('all') && $currentImage && $currentImage !== self::DEFAULT_IMAGE && $disk->exists($currentImage)) {
                $disk->delete($currentImage);
            }

            $image = $pool[$cursor++];
            $extension = strtolower(pathinfo($image, PATHINFO_EXTENSION));
            $name = now()->format('Ymd_His') . '_' . uniqid() . '.' . $extension;
            $path = 'profile_images/' . $name;

            if (!$disk->copy($image, $path)) {
                $this->warn("Failed to copy [{$image}] for user #{$userId}, skipped.");
                continue;
            }

            DB::table('users')->where('id', $userId)->update(['profile_image' => $path]);
            $updated++;
        }

        $this->info("Updated {$updated} users using {$images->count()} source images.");

        return self::SUCCESS;
    }
}
