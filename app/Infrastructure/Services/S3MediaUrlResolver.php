<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Domain\Interfaces\MediaUrlResolverInterface;
use Illuminate\Support\Facades\Storage;

class S3MediaUrlResolver implements MediaUrlResolverInterface
{
    public function resolve(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        $disk = Storage::disk('s3');

        try {
            return $disk->temporaryUrl($path, now()->addHours(24));
        } catch (\Exception) {
            // fall through
        }

        try {
            return $disk->url($path);
        } catch (\Exception) {
            // fall through
        }

        $endpoint = rtrim((string) config('filesystems.disks.s3.endpoint', ''), '/');
        $bucket = (string) config('filesystems.disks.s3.bucket', '');

        if ($endpoint !== '' && $bucket !== '') {
            return "{$endpoint}/{$bucket}/".ltrim($path, '/');
        }

        return null;
    }
}
