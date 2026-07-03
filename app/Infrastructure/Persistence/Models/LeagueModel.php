<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeagueModel extends Model
{
    protected $table = 'leagues';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'edition_id',
        'scoring_system_id',
        'owner_id',
        'invite_code',
        'max_players',
        'is_public',
        'is_official',
    ];

    protected $casts = [
        'max_players' => 'integer',
        'is_public' => 'boolean',
        'is_official' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(EditionModel::class, 'edition_id', 'id');
    }

    public function scoringSystem(): BelongsTo
    {
        return $this->belongsTo(ScoringSystemModel::class, 'scoring_system_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'league_user', 'league_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function stages(): HasMany
    {
        return $this->hasMany(StageModel::class, 'edition_id', 'edition_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLogModel::class, 'league_id', 'id');
    }
}
