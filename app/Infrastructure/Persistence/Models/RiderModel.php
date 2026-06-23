<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RiderModel extends Model
{
    protected $table = 'riders';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'nationality',
        'birth_date',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $rider): void {
            if (empty($rider->id)) {
                $rider->id = Str::uuid()->toString();
            }
        });
    }

    public function rosters(): HasMany
    {
        return $this->hasMany(TeamRosterModel::class, 'rider_id', 'id');
    }

    public function competitionParticipants(): HasMany
    {
        return $this->hasMany(CompetitionParticipantModel::class, 'rider_id', 'id');
    }
}
