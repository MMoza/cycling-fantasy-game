<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BadgeEventModel extends Model
{
    protected $table = 'badge_events';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'user_id',
        'badge_code',
        'event',
        'level',
        'progress',
        'context',
        'created_at',
    ];

    protected $casts = [
        'level' => 'integer',
        'progress' => 'integer',
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
