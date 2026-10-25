<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFinalClassificationsRequest extends FormRequest
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
            'classifications' => 'required|array',
            'classifications.gc_top_5' => 'nullable|array|size:5',
            'classifications.gc_top_5.*' => 'required|string|exists:riders,id',
            'classifications.points_winner' => 'nullable|array|size:3',
            'classifications.points_winner.*' => 'required|string|exists:riders,id',
            'classifications.mountains_winner' => 'nullable|array|size:3',
            'classifications.mountains_winner.*' => 'required|string|exists:riders,id',
            'classifications.youth_winner' => 'nullable|array|size:3',
            'classifications.youth_winner.*' => 'required|string|exists:riders,id',
            'classifications.teams_winner' => 'nullable|string|exists:teams,id',
            'classifications.super_combativo' => 'nullable|string|exists:riders,id',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function classifications(): array
    {
        return $this->validated('classifications');
    }
}
