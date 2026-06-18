<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Domain\ValueObjects\EditionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EditionModel extends Model
{
    protected $table = 'editions';

    protected $fillable = [
        'id',
        'competition_id',
        'year',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'year' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => EditionStatus::class,
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(CompetitionModel::class);
    }

    public function leagues(): HasMany
    {
        return $this->hasMany(LeagueModel::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(StageModel::class);
    }
}
