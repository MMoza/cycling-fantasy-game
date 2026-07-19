<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Domain\ValueObjects\CompetitionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

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
        'cover_image',
        'logo_image',
        'pcs_slug',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $competition): void {
            if (empty($competition->id)) {
                $competition->id = Str::uuid()->toString();
            }
        });
    }

    public function country(): BelongsTo
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
