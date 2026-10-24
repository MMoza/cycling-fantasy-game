<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreStageResultRequest extends FormRequest
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
            'results' => 'required|array|min:1',
            'results.*.rider_id' => 'required|string',
            'results.*.position' => 'required|integer|min:1',
            'results.*.time' => 'nullable|string|max:50',
            'results.*.gap' => 'nullable|string|max:50',
            'results.*.is_gc_leader' => 'nullable|boolean',
            'results.*.is_combativo' => 'nullable|boolean',
        ];
    }
}
