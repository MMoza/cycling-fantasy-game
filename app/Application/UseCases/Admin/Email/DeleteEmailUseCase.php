<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Email;

use App\Application\Exceptions\ApplicationException;
use App\Infrastructure\Persistence\Models\ScheduledEmailModel;

final class DeleteEmailUseCase
{
    public function execute(string $id): void
    {
        $email = ScheduledEmailModel::findOrFail($id);

        if (! $email->status->isDeletable()) {
            throw new ApplicationException('No se puede eliminar un email ya enviado');
        }

        $email->delete();
    }
}
