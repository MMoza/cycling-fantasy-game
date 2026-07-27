<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use Illuminate\Support\Str;

final class InvitationCode
{
    private function __construct(
        public readonly string $value,
    ) {}

    public static function generate(): self
    {
        return new self(Str::upper(Str::random(12)));
    }

    public static function fromString(string $value): self
    {
        if (strlen($value) !== 12) {
            throw new \InvalidArgumentException('Invitation code must be 12 characters');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
