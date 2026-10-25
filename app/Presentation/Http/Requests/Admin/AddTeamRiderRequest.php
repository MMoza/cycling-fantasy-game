<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AddTeamRiderRequest extends FormRequest
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
            'rider_id' => 'required|string|exists:riders,id',
            'year' => 'required|integer|min:2020|max:2100',
        ];
    }

    public function riderId(): string
    {
        return $this->validated('rider_id');
    }

    public function year(): int
    {
        return $this->validated('year');
    }
}
