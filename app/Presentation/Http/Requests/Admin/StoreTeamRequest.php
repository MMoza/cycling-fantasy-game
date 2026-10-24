<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'abbreviation' => 'nullable|string|size:3|unique:teams,abbreviation',
            'country_id' => 'nullable|string|size:2|exists:countries,id',
            'logo_url' => 'nullable|string|max:255',
        ];
    }
}
