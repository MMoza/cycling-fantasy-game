<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class StageModel extends Model
{
    protected $table = 'stages';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'edition_id',
        'number',
        'name',
        'date',
        'scheduled_start',
        'type',
        'distance',
        'origin',
        'destination',
        'status',
        'elevation_gain',
        'profile_image',
        'difficulty',
        'live_stream_url',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $stage): void {
            if (empty($stage->id)) {
                $stage->id = Str::uuid()->toString();
            }
        });
    }

    protected $casts = [
        'number' => 'integer',
        'date' => 'date',
        'scheduled_start' => 'datetime',
        'distance' => 'float',
        'elevation_gain' => 'integer',
        'difficulty' => 'integer',
        'type' => StageType::class,
        'status' => StageStatus::class,
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(EditionModel::class, 'edition_id', 'id');
    }
}
