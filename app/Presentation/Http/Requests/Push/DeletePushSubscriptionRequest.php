<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Push;

use Illuminate\Foundation\Http\FormRequest;

class DeletePushSubscriptionRequest extends FormRequest
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
            'endpoint' => 'required|string',
        ];
    }

    public function endpoint(): string
    {
        return $this->validated('endpoint');
    }
}
