<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Domain\ValueObjects\CompetitionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionModel extends Model
{
    protected $table = 'competitions';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'type',
        'country',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'type' => CompetitionType::class,
    ];

    public function editions(): HasMany
    {
        return $this->hasMany(EditionModel::class, 'competition_id', 'id');
    }
}
