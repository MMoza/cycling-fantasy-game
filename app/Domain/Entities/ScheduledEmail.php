<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\EmailRecipients;
use App\Domain\ValueObjects\EmailStatus;
use Carbon\Carbon;

final class ScheduledEmail
{
    private function __construct(
        public readonly string $id,
        public readonly string $subject,
        public readonly string $bodyHtml,
        public readonly EmailRecipients $recipients,
        public readonly ?array $recipientIds,
        public readonly ?Carbon $scheduledAt,
        public readonly EmailStatus $status,
        public readonly ?Carbon $sentAt,
        public readonly int $sentCount,
        public readonly ?string $errorMessage,
        public readonly string $createdBy,
        public readonly Carbon $createdAt,
        public readonly Carbon $updatedAt,
    ) {}

    public static function create(
        string $id,
        string $subject,
        string $bodyHtml,
        EmailRecipients $recipients,
        ?array $recipientIds,
        ?Carbon $scheduledAt,
        string $createdBy,
    ): self {
        $status = $scheduledAt !== null ? EmailStatus::Scheduled : EmailStatus::Draft;

        return new self(
            id: $id,
            subject: $subject,
            bodyHtml: $bodyHtml,
            recipients: $recipients,
            recipientIds: $recipientIds,
            scheduledAt: $scheduledAt,
            status: $status,
            sentAt: null,
            sentCount: 0,
            errorMessage: null,
            createdBy: $createdBy,
            createdAt: Carbon::now(),
            updatedAt: Carbon::now(),
        );
    }

    public function markAsSent(int $count): self
    {
        return new self(
            id: $this->id,
            subject: $this->subject,
            bodyHtml: $this->bodyHtml,
            recipients: $this->recipients,
            recipientIds: $this->recipientIds,
            scheduledAt: $this->scheduledAt,
            status: EmailStatus::Sent,
            sentAt: Carbon::now(),
            sentCount: $count,
            errorMessage: null,
            createdBy: $this->createdBy,
            createdAt: $this->createdAt,
            updatedAt: Carbon::now(),
        );
    }

    public function markAsFailed(string $error): self
    {
        return new self(
            id: $this->id,
            subject: $this->subject,
            bodyHtml: $this->bodyHtml,
            recipients: $this->recipients,
            recipientIds: $this->recipientIds,
            scheduledAt: $this->scheduledAt,
            status: EmailStatus::Failed,
            sentAt: null,
            sentCount: 0,
            errorMessage: $error,
            createdBy: $this->createdBy,
            createdAt: $this->createdAt,
            updatedAt: Carbon::now(),
        );
    }

    public function isEditable(): bool
    {
        return $this->status === EmailStatus::Draft || $this->status === EmailStatus::Scheduled;
    }

    public function isDeletable(): bool
    {
        return $this->status !== EmailStatus::Sent;
    }
}
