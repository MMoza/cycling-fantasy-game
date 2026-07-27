<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Domain\ValueObjects\EmailStatus;
use App\Infrastructure\Persistence\Models\ScheduledEmailModel;
use App\Jobs\SendScheduledEmailJob;
use Illuminate\Console\Command;

class SendScheduledEmailsCommand extends Command
{
    protected $signature = 'mail:send-scheduled';

    protected $description = 'Send scheduled emails that are due';

    public function handle(): int
    {
        $emails = ScheduledEmailModel::where('status', EmailStatus::Scheduled)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($emails->isEmpty()) {
            $this->info('No scheduled emails to send.');

            return self::SUCCESS;
        }

        foreach ($emails as $email) {
            SendScheduledEmailJob::dispatch($email->id);
            $this->info("Dispatched email: {$email->subject}");
        }

        $this->info("Dispatched {$emails->count()} email(s).");

        return self::SUCCESS;
    }
}
