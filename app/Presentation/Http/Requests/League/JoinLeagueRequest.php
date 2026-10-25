<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\League;

use Illuminate\Foundation\Http\FormRequest;

class JoinLeagueRequest extends FormRequest
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
            'invite_code' => ['required', 'string', 'exists:leagues,invite_code'],
        ];
    }

    public function inviteCode(): string
    {
        return $this->validated('invite_code');
    }
}
