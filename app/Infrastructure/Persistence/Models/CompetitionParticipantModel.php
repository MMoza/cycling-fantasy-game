<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CompetitionParticipantModel extends Model
{
    protected $table = 'competition_participants';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'competition_id',
        'edition_id',
        'team_id',
        'rider_id',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $participant): void {
            if (empty($participant->id)) {
                $participant->id = Str::uuid()->toString();
            }
        });
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(CompetitionModel::class, 'competition_id', 'id');
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(EditionModel::class, 'edition_id', 'id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(TeamModel::class, 'team_id', 'id');
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(RiderModel::class, 'rider_id', 'id');
    }
}
