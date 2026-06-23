<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Domain\ValueObjects\ScoringRuleContext;
use App\Domain\ValueObjects\ScoringRuleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoringRuleModel extends Model
{
    protected $table = 'scoring_rules';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'scoring_system_id',
        'type',
        'context',
        'points',
    ];

    protected $casts = [
        'type' => ScoringRuleType::class,
        'context' => ScoringRuleContext::class,
        'points' => 'integer',
    ];

    public function scoringSystem(): BelongsTo
    {
        return $this->belongsTo(ScoringSystemModel::class, 'scoring_system_id', 'id');
    }
}
