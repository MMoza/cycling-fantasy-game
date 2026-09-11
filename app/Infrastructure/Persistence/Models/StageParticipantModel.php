<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class StageParticipantModel extends Model
{
    protected $table = 'stage_participants';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'stage_id',
        'rider_id',
        'team_id',
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

    public function stage(): BelongsTo
    {
        return $this->belongsTo(StageModel::class, 'stage_id', 'id');
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(RiderModel::class, 'rider_id', 'id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(TeamModel::class, 'team_id', 'id');
    }
}
