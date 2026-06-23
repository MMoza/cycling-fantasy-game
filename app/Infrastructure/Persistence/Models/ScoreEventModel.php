<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoreEventModel extends Model
{
    protected $table = 'score_events';

    public $incrementing = false;

    protected $keyType = 'string';

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
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(LeagueModel::class, 'league_id', 'id');
    }
}
