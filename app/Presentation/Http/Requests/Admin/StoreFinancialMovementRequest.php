<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Admin;

use App\Application\DTOs\Finance\StoreMovementDTO;
use App\Domain\ValueObjects\MovementCategory;
use App\Domain\ValueObjects\MovementType;
use App\Presentation\Http\Rules\MovementCategoryRule;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialMovementRequest extends FormRequest
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
            'type' => ['required', 'string', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'concept' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:60', new MovementCategoryRule($this->input('type'))],
            'occurred_on' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function toDTO(): StoreMovementDTO
    {
        $validated = $this->validated();

        return new StoreMovementDTO(
            type: MovementType::from($validated['type']),
            amount: (string) $validated['amount'],
            concept: $validated['concept'],
            category: MovementCategory::from($validated['category']),
            occurredOn: CarbonImmutable::parse($validated['occurred_on']),
            notes: $validated['notes'] ?? null,
            createdBy: $this->user()->id,
        );
    }
}
