<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Domain\ValueObjects\ActivityLogType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLogModel extends Model
{
    protected $table = 'activity_logs';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'league_id',
        'type',
        'title',
        'description',
        'data',
    ];

    protected $casts = [
        'type' => ActivityLogType::class,
        'data' => 'array',
    ];

    public function league(): BelongsTo
    {
        return $this->belongsTo(LeagueModel::class, 'league_id', 'id');
    }
}
