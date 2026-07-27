<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\ValueObjects\EmailRecipients;
use App\Domain\ValueObjects\EmailStatus;
use App\Infrastructure\Persistence\Models\ScheduledEmailModel;
use App\Mail\AdminMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendScheduledEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly string $emailId,
    ) {}

    public function handle(): void
    {
        $email = ScheduledEmailModel::find($this->emailId);

        if (! $email) {
            return;
        }

        if ($email->status === EmailStatus::Sent) {
            return;
        }

        try {
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
        } catch (\Throwable $e) {
            $email->update([
                'status' => EmailStatus::Failed,
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
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
