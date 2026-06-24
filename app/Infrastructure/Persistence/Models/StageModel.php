<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

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
