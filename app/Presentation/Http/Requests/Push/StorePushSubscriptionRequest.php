<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Push;

use Illuminate\Foundation\Http\FormRequest;

class StorePushSubscriptionRequest extends FormRequest
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
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ];
    }

    public function endpoint(): string
    {
        return $this->validated('endpoint');
    }

    public function p256dh(): string
    {
        return $this->validated('keys.p256dh');
    }

    public function auth(): string
    {
        return $this->validated('keys.auth');
    }
}
