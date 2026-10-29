<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Season;

use Illuminate\Foundation\Http\FormRequest;

class JoinAllSeasonRequest extends FormRequest
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
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ];
    }

    public function year(): int
    {
        return (int) $this->validated('year');
    }
}
