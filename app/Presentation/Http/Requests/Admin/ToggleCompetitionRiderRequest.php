<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ToggleCompetitionRiderRequest extends FormRequest
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
            'rider_id' => 'required|string|exists:riders,id',
            'active' => 'required|boolean',
        ];
    }

    public function teamId(): string
    {
        return $this->validated('team_id');
    }

    public function riderId(): string
    {
        return $this->validated('rider_id');
    }

    public function active(): bool
    {
        return $this->validated('active');
    }
}
