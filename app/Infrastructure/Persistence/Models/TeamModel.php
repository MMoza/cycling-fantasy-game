<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TeamModel extends Model
{
    protected $table = 'teams';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'abbreviation',
        'country_id',
        'logo_url',
    ];

    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CountryModel::class, 'country_id', 'id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $team): void {
            if (empty($team->id)) {
                $team->id = Str::uuid()->toString();
            }
        });
    }

    public function rosters(): HasMany
    {
        return $this->hasMany(TeamRosterModel::class, 'team_id', 'id');
    }

    public function competitionParticipants(): HasMany
    {
        return $this->hasMany(CompetitionParticipantModel::class, 'team_id', 'id');
    }
}
