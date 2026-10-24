<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Prediction;

use Illuminate\Foundation\Http\FormRequest;

class StoreStagePredictionRequest extends FormRequest
{
    private const CATEGORIES = [
        'stage_winner',
        'stage_second',
        'stage_third',
        'stage_leader',
        'stage_combativo',
        'stage_position_1',
        'stage_position_2',
        'stage_position_3',
        'stage_position_4',
        'stage_position_5',
        'stage_position_6',
        'stage_position_7',
        'stage_position_8',
        'stage_position_9',
        'stage_position_10',
    ];

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
            'predictions' => ['required', 'array'],
            'predictions.*.category' => ['required', 'string', 'in:'.implode(',', self::CATEGORIES)],
            'predictions.*.value' => ['required'],
        ];
    }

    /**
     * Predictions with a non-empty value.
     *
     * @return array<int, mixed>
     */
    public function predictions(): array
    {
        return array_values(array_filter(
            $this->validated('predictions'),
            fn (array $prediction) => ! empty($prediction['value']),
        ));
    }
}
