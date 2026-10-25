<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AddCompetitionTeamRequest extends FormRequest
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
            'team_id' => 'required|string|exists:teams,id',
        ];
    }

    public function teamId(): string
    {
        return $this->validated('team_id');
    }
}
