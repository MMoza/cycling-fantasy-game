<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LeagueModel extends Model
{
    protected $table = 'leagues';

    protected $fillable = [
        'id',
        'name',
        'edition_id',
        'scoring_system_id',
        'owner_id',
        'invite_code',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(EditionModel::class);
    }

    public function scoringSystem(): BelongsTo
    {
        return $this->belongsTo(ScoringSystemModel::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'league_user', 'league_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }
}
