<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompetitionRequest extends FormRequest
{
    private const ALLOWED_TYPES = 'gc,major,monument,classic,championship';

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
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:'.self::ALLOWED_TYPES,
            'country_id' => 'required|string|size:2|exists:countries,id',
            'active' => 'boolean',
            'cover_image' => 'nullable|image|mimes:jpeg,png,webp|max:4096',
            'logo_image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'pcs_slug' => 'nullable|string|max:255',
            'remove_cover_image' => 'boolean',
            'remove_logo_image' => 'boolean',
        ];
    }
}
