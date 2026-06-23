<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TeamRosterModel extends Model
{
    protected $table = 'team_rosters';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'team_id',
        'rider_id',
        'year',
    ];

    protected $casts = [
        'year' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $roster): void {
            if (empty($roster->id)) {
                $roster->id = Str::uuid()->toString();
            }
        });
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
