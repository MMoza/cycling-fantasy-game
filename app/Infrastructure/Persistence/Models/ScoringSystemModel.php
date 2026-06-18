<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Domain\ValueObjects\ScoringSystemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScoringSystemModel extends Model
{
    protected $table = 'scoring_systems';

    protected $fillable = [
        'id',
        'name',
        'type',
        'description',
    ];

    protected $casts = [
        'type' => ScoringSystemType::class,
    ];

    public function rules(): HasMany
    {
        return $this->hasMany(ScoringRuleModel::class);
    }
}
