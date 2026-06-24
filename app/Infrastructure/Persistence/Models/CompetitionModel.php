<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Domain\ValueObjects\CompetitionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class CompetitionModel extends Model
{
    protected $table = 'competitions';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'type',
        'country_id',
        'active',
    ];

    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CountryModel::class, 'country_id', 'id');
    }

    protected $casts = [
        'active' => 'boolean',
        'type' => CompetitionType::class,
    ];

    public function editions(): HasMany
    {
        return $this->hasMany(EditionModel::class, 'competition_id', 'id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CompetitionParticipantModel::class, 'competition_id', 'id');
    }

    public function teams(): HasManyThrough
    {
        return $this->hasManyThrough(
            TeamModel::class,
            CompetitionParticipantModel::class,
            'competition_id',
            'id',
            'id',
            'team_id'
        )->distinct();
    }
}
