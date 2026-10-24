<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\League;

use App\Application\DTOs\CreateLeagueDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeagueRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'edition_id' => ['required', 'uuid', 'exists:editions,id'],
            'scoring_system_id' => ['required', 'uuid', 'exists:scoring_systems,id'],
            'is_public' => ['required', 'boolean'],
            'is_official' => ['sometimes', 'boolean'],
        ];
    }

    public function toDTO(): CreateLeagueDTO
    {
        $validated = $this->validated();

        return new CreateLeagueDTO(
            name: $validated['name'],
            editionId: $validated['edition_id'],
            scoringSystemId: $validated['scoring_system_id'],
            isPublic: $validated['is_public'],
            isOfficial: $validated['is_official'] ?? false,
        );
    }
}
