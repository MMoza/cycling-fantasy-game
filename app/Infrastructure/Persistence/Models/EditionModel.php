<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Domain\ValueObjects\EditionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EditionModel extends Model
{
    protected $table = 'editions';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'competition_id',
        'year',
        'start_date',
        'end_date',
        'status',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $edition): void {
            if (empty($edition->id)) {
                $edition->id = Str::uuid()->toString();
            }
        });
    }

    protected $casts = [
        'year' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => EditionStatus::class,
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(CompetitionModel::class, 'competition_id', 'id');
    }

    public function leagues(): HasMany
    {
        return $this->hasMany(LeagueModel::class, 'edition_id', 'id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(StageModel::class, 'edition_id', 'id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CompetitionParticipantModel::class, 'edition_id', 'id');
    }
}
