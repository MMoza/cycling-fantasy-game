<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Domain\ValueObjects\PredictionCategory;
use App\Domain\ValueObjects\PredictionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PredictionModel extends Model
{
    protected $table = 'predictions';

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
        return $this->belongsTo(User::class);
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(LeagueModel::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(StageModel::class);
    }
}
