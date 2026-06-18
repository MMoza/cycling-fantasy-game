<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StageResultModel extends Model
{
    protected $table = 'stage_results';

    protected $fillable = [
        'id',
        'stage_id',
        'rider_id',
        'position',
        'time',
        'gap',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(StageModel::class);
    }
}
