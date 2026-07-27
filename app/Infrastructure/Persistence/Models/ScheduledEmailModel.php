<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Domain\Entities\ScheduledEmail;
use App\Domain\ValueObjects\EmailRecipients;
use App\Domain\ValueObjects\EmailStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledEmailModel extends Model
{
    protected $table = 'scheduled_emails';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'subject',
        'body_html',
        'recipients',
        'recipient_ids',
        'scheduled_at',
        'status',
        'sent_at',
        'sent_count',
        'error_message',
        'created_by',
    ];

    protected $casts = [
        'recipient_ids' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'sent_count' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function getRecipientsAttribute(): EmailRecipients
    {
        return EmailRecipients::from($this->attributes['recipients']);
    }

    public function setRecipientsAttribute(EmailRecipients|string $value): void
    {
        $this->attributes['recipients'] = $value instanceof EmailRecipients ? $value->value : $value;
    }

    public function getStatusAttribute(): EmailStatus
    {
        return EmailStatus::from($this->attributes['status']);
    }

    public function setStatusAttribute(EmailStatus|string $value): void
    {
        $this->attributes['status'] = $value instanceof EmailStatus ? $value->value : $value;
    }

    public function toDomain(): ScheduledEmail
    {
        return ScheduledEmail::create(
            id: $this->id,
            subject: $this->subject,
            bodyHtml: $this->body_html,
            recipients: $this->recipients,
            recipientIds: $this->recipient_ids,
            scheduledAt: $this->scheduled_at ? Carbon::parse($this->scheduled_at) : null,
            createdBy: $this->created_by,
        );
    }
}
