<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

trait HandleUserImage
{
    public function uploadUserImage(UploadedFile $image): string
    {
        $name = now()->format('Ymd_His') . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        return $image->storeAs('profile_images', $name, 'public');
    }

    public function defaultUserImage(): string
    {
        return 'defaults/avatar.svg';
    }

    public function handleUserImage(?UploadedFile $image): string
    {
        return $image ? $this->uploadUserImage($image) : $this->defaultUserImage();
    }

    public function deleteUserImage(?string $path): void
    {
        if ($path && $path !== $this->defaultUserImage() && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function getUserImageUrl(?string $path): string
    {
        return asset('storage/' . ($path ?? $this->defaultUserImage()));
    }
}
