<?php

declare(strict_types=1);

namespace App\Application\DTOs\Email;

use App\Domain\ValueObjects\EmailRecipients;
use Carbon\Carbon;

final readonly class UpdateEmailDTO
{
    public function __construct(
        public string $id,
        public string $subject,
        public string $bodyHtml,
        public EmailRecipients $recipients,
        public ?array $recipientIds,
        public ?Carbon $scheduledAt,
    ) {}
}
