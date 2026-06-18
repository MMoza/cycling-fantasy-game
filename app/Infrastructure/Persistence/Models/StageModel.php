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

    protected $fillable = [
        'id',
        'edition_id',
        'number',
        'name',
        'date',
        'type',
        'distance',
        'origin',
        'destination',
        'status',
    ];

    protected $casts = [
        'number' => 'integer',
        'date' => 'date',
        'distance' => 'float',
        'type' => StageType::class,
        'status' => StageStatus::class,
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(EditionModel::class);
    }
}
