<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRiderRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'country_id' => 'nullable|string|size:2|exists:countries,id',
            'birth_date' => 'nullable|date',
            'profile_image' => 'nullable|string|max:255',
        ];
    }
}
