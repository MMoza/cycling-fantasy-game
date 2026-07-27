<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Email;

use App\Application\DTOs\Email\StoreEmailDTO;
use App\Infrastructure\Persistence\Models\ScheduledEmailModel;
use Illuminate\Support\Str;

final class StoreEmailUseCase
{
    public function execute(StoreEmailDTO $dto): ScheduledEmailModel
    {
        return ScheduledEmailModel::create([
            'id' => Str::uuid()->toString(),
            'subject' => $dto->subject,
            'body_html' => $dto->bodyHtml,
            'recipients' => $dto->recipients,
            'recipient_ids' => $dto->recipientIds,
            'scheduled_at' => $dto->scheduledAt,
            'status' => $dto->scheduledAt !== null ? 'scheduled' : 'draft',
            'created_by' => $dto->createdBy,
        ]);
    }
}
