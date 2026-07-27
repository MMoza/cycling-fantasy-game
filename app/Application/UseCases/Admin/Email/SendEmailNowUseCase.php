<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Email;

use App\Application\Exceptions\ApplicationException;
use App\Infrastructure\Persistence\Models\ScheduledEmailModel;
use App\Jobs\SendScheduledEmailJob;

final class SendEmailNowUseCase
{
    public function execute(string $id): void
    {
        $email = ScheduledEmailModel::findOrFail($id);

        if ($email->status->value === 'sent') {
            throw new ApplicationException('Este email ya fue enviado');
        }

        SendScheduledEmailJob::dispatch($email->id);
    }
}
