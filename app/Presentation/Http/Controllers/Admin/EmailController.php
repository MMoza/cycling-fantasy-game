<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\DTOs\Email\StoreEmailDTO;
use App\Application\DTOs\Email\UpdateEmailDTO;
use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\Admin\Email\DeleteEmailUseCase;
use App\Application\UseCases\Admin\Email\ListEmailsUseCase;
use App\Application\UseCases\Admin\Email\SendEmailNowUseCase;
use App\Application\UseCases\Admin\Email\StoreEmailUseCase;
use App\Application\UseCases\Admin\Email\UpdateEmailUseCase;
use App\Domain\ValueObjects\EmailRecipients;
use App\Infrastructure\Persistence\Models\ScheduledEmailModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailController
{
    public function __construct(
        private readonly ListEmailsUseCase $listEmailsUseCase,
        private readonly StoreEmailUseCase $storeEmailUseCase,
        private readonly UpdateEmailUseCase $updateEmailUseCase,
        private readonly DeleteEmailUseCase $deleteEmailUseCase,
        private readonly SendEmailNowUseCase $sendEmailNowUseCase,
    ) {}

    public function index(Request $request): Response
    {
        $status = $request->query('status');
        $emails = $this->listEmailsUseCase->execute($status);

        return Inertia::render('Admin/Emails/Index', [
            'emails' => $emails->map(fn (ScheduledEmailModel $email) => [
                'id' => $email->id,
                'subject' => $email->subject,
                'recipients' => $email->recipients->label(),
                'scheduled_at' => $email->scheduled_at?->format('d/m/Y H:i'),
                'status' => $email->status->value,
                'status_label' => $email->status->label(),
                'sent_at' => $email->sent_at?->format('d/m/Y H:i'),
                'sent_count' => $email->sent_count,
                'error_message' => $email->error_message,
                'created_by' => $email->creator?->name ?? 'Unknown',
                'created_at' => $email->created_at->format('d/m/Y H:i'),
            ]),
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    public function create(): Response
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        return Inertia::render('Admin/Emails/Form', [
            'email' => null,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body_html' => ['required', 'string'],
            'recipients' => ['required', 'string', 'in:all_users,custom'],
            'recipient_ids' => ['nullable', 'array'],
            'recipient_ids.*' => ['uuid', 'exists:users,id'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ]);

        $dto = new StoreEmailDTO(
            subject: $validated['subject'],
            bodyHtml: $validated['body_html'],
            recipients: EmailRecipients::from($validated['recipients']),
            recipientIds: $validated['recipient_ids'] ?? null,
            scheduledAt: isset($validated['scheduled_at']) ? Carbon::parse($validated['scheduled_at']) : null,
            createdBy: $request->user()->id,
        );

        $email = $this->storeEmailUseCase->execute($dto);

        return redirect()->route('admin.emails.index')
            ->with('success', 'Email creado correctamente');
    }

    public function edit(string $email): Response
    {
        $emailModel = ScheduledEmailModel::findOrFail($email);
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        return Inertia::render('Admin/Emails/Form', [
            'email' => [
                'id' => $emailModel->id,
                'subject' => $emailModel->subject,
                'body_html' => $emailModel->body_html,
                'recipients' => $emailModel->recipients->value,
                'recipient_ids' => $emailModel->recipient_ids,
                'scheduled_at' => $emailModel->scheduled_at?->toIso8601String(),
                'status' => $emailModel->status->value,
            ],
            'users' => $users,
        ]);
    }

    public function update(Request $request, string $email)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body_html' => ['required', 'string'],
            'recipients' => ['required', 'string', 'in:all_users,custom'],
            'recipient_ids' => ['nullable', 'array'],
            'recipient_ids.*' => ['uuid', 'exists:users,id'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ]);

        try {
            $dto = new UpdateEmailDTO(
                id: $email,
                subject: $validated['subject'],
                bodyHtml: $validated['body_html'],
                recipients: EmailRecipients::from($validated['recipients']),
                recipientIds: $validated['recipient_ids'] ?? null,
                scheduledAt: isset($validated['scheduled_at']) ? Carbon::parse($validated['scheduled_at']) : null,
            );

            $this->updateEmailUseCase->execute($dto);

            return redirect()->route('admin.emails.index')
                ->with('success', 'Email actualizado correctamente');
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['email' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(string $email)
    {
        try {
            $this->deleteEmailUseCase->execute($email);

            return redirect()->route('admin.emails.index')
                ->with('success', 'Email eliminado correctamente');
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['email' => $e->getMessage()]);
        }
    }

    public function sendNow(string $email)
    {
        try {
            $this->sendEmailNowUseCase->execute($email);

            return redirect()->route('admin.emails.index')
                ->with('success', 'Email enviado a la cola de procesamiento');
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['email' => $e->getMessage()]);
        }
    }
}
