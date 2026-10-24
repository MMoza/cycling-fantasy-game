<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Admin;

use App\Application\DTOs\Email\StoreEmailDTO;
use App\Domain\ValueObjects\EmailRecipients;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'body_html' => ['required', 'string'],
            'recipients' => ['required', 'string', 'in:all_users,custom'],
            'recipient_ids' => ['nullable', 'array'],
            'recipient_ids.*' => ['uuid', 'exists:users,id'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    public function toDTO(): StoreEmailDTO
    {
        $validated = $this->validated();

        return new StoreEmailDTO(
            subject: $validated['subject'],
            bodyHtml: $validated['body_html'],
            recipients: EmailRecipients::from($validated['recipients']),
            recipientIds: $validated['recipient_ids'] ?? null,
            scheduledAt: isset($validated['scheduled_at']) ? Carbon::parse($validated['scheduled_at']) : null,
            createdBy: $this->user()->id,
        );
    }
}
