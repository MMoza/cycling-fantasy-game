<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

interface MediaUrlResolverInterface
{
    /**
     * Resolve a stored media path (avatar, cover, logo…) to a usable URL.
     *
     * Absolute http(s) paths are returned untouched. Stored paths are resolved
     * to a temporary URL when the disk supports it, falling back to the disk
     * public URL and finally to an endpoint/bucket derived URL when configured.
     * Returns null when the path is empty or cannot be resolved.
     */
    public function resolve(?string $path): ?string;
}
