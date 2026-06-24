<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RiderModel extends Model
{
    protected $table = 'riders';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'country_id',
        'birth_date',
        'profile_image',
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

    public function getFullNameAttribute(): string
    {
        return trim("{$this->last_name} {$this->first_name}");
    }

    public function getAgeAttribute(): ?int
    {
        return (int) $this->birth_date?->diffInYears(CarbonImmutable::today());
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(CountryModel::class, 'country_id', 'id');
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
