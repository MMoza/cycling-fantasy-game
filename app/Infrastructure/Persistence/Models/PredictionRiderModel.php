<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class PredictionRiderModel extends Model
{
    protected $table = 'prediction_riders';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'prediction_id',
        'rider_id',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];
}
