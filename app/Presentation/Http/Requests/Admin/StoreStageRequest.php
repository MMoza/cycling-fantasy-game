<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreStageRequest extends FormRequest
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
            'number' => 'required|integer|min:1',
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|string',
            'distance' => 'nullable|numeric|min:0',
            'elevation_gain' => 'nullable|integer|min:0',
            'difficulty' => 'nullable|integer|min:1|max:3',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'scheduled_start' => 'nullable|date',
            'profile_image' => 'nullable|url|max:2048',
            'live_stream_url' => 'nullable|url|max:2048',
            'rider_ids' => 'nullable|array',
            'rider_ids.*' => 'string',
        ];
    }
}
