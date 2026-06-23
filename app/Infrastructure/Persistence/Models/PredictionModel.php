<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Domain\ValueObjects\PredictionCategory;
use App\Domain\ValueObjects\PredictionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PredictionModel extends Model
{
    protected $table = 'predictions';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'league_id',
        'type',
        'category',
        'stage_id',
        'prediction_value',
        'locked_at',
    ];

    protected $casts = [
        'type' => PredictionType::class,
        'category' => PredictionCategory::class,
        'prediction_value' => 'array',
        'locked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(LeagueModel::class, 'league_id', 'id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(StageModel::class, 'stage_id', 'id');
    }
}
