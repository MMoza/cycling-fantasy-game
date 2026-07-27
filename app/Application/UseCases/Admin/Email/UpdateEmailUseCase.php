<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Email;

use App\Application\DTOs\Email\UpdateEmailDTO;
use App\Application\Exceptions\ApplicationException;
use App\Domain\ValueObjects\EmailStatus;
use App\Infrastructure\Persistence\Models\ScheduledEmailModel;

final class UpdateEmailUseCase
{
    public function execute(UpdateEmailDTO $dto): ScheduledEmailModel
    {
        $email = ScheduledEmailModel::findOrFail($dto->id);

        if (! $email->status->isEditable()) {
            throw new ApplicationException('No se puede editar un email ya enviado');
        }

        $email->update([
            'subject' => $dto->subject,
            'body_html' => $dto->bodyHtml,
            'recipients' => $dto->recipients,
            'recipient_ids' => $dto->recipientIds,
            'scheduled_at' => $dto->scheduledAt,
            'status' => $dto->scheduledAt !== null ? EmailStatus::Scheduled : EmailStatus::Draft,
        ]);

        return $email->fresh();
    }
}
