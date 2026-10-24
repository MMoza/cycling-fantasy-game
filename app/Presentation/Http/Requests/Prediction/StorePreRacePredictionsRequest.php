<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Prediction;

use App\Infrastructure\Persistence\Models\LeagueModel;
use Illuminate\Foundation\Http\FormRequest;

class StorePreRacePredictionsRequest extends FormRequest
{
    private const CATEGORIES = [
        'gc_top_5',
        'points_winner',
        'youth_winner',
        'mountains_winner',
        'teams_winner',
        'super_combativo',
    ];

    private const ONE_DAY_RACE_TYPES = ['monument', 'championship', 'classic'];

    public function authorize(): bool
    {
        if ($this->user() === null) {
            return false;
        }

        $league = LeagueModel::with('edition.competition')->findOrFail($this->route('league'));

        abort_if(
            in_array($league->edition->competition->type->value, self::ONE_DAY_RACE_TYPES, true),
            404,
        );

        return true;
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
     * @return array<int, mixed>
     */
    public function predictions(): array
    {
        return $this->validated('predictions');
    }
}
