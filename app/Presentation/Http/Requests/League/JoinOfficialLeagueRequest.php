<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\League;

use Illuminate\Foundation\Http\FormRequest;

class JoinOfficialLeagueRequest extends FormRequest
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
            'league_id' => ['required', 'uuid', 'exists:leagues,id'],
        ];
    }

    public function leagueId(): string
    {
        return $this->validated('league_id');
    }
}
