<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoreEventModel extends Model
{
    protected $table = 'score_events';

    protected $fillable = [
        'id',
        'user_id',
        'league_id',
        'scoring_rule_id',
        'points',
        'description',
        'context',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(LeagueModel::class);
    }
}
