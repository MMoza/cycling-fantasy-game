<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Email;

use App\Application\Exceptions\ApplicationException;
use App\Domain\ValueObjects\EmailRecipients;
use App\Domain\ValueObjects\EmailStatus;
use App\Infrastructure\Persistence\Models\ScheduledEmailModel;
use App\Mail\AdminMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

final class SendEmailNowUseCase
{
    public function execute(string $id): void
    {
        $email = ScheduledEmailModel::findOrFail($id);

        if ($email->status->value === 'sent') {
            throw new ApplicationException('Este email ya fue enviado');
        }

        $recipients = $this->resolveRecipients($email);

        $count = 0;

        foreach ($recipients as $recipient) {
            Mail::to($recipient)->send(new AdminMail($email->subject, $email->body_html));
            $count++;
        }

        $email->update([
            'status' => EmailStatus::Sent,
            'sent_at' => now(),
            'sent_count' => $count,
            'error_message' => null,
        ]);
    }

    private function resolveRecipients(ScheduledEmailModel $email): array
    {
        if ($email->recipients === EmailRecipients::AllUsers) {
            return User::pluck('email')->filter()->toArray();
        }

        if ($email->recipients === EmailRecipients::Custom && $email->recipient_ids !== null) {
            return User::whereIn('id', $email->recipient_ids)
                ->pluck('email')
                ->filter()
                ->toArray();
        }

        return [];
    }
}
