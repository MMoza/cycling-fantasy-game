<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalClassificationModel extends Model
{
    protected $table = 'final_classifications';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'edition_id',
        'category',
        'rider_id',
        'team_id',
        'position',
    ];

    /** @return BelongsTo<EditionModel, $this> */
    public function edition(): BelongsTo
    {
        return $this->belongsTo(EditionModel::class);
    }

    /** @return BelongsTo<RiderModel, $this> */
    public function rider(): BelongsTo
    {
        return $this->belongsTo(RiderModel::class);
    }

    /** @return BelongsTo<TeamModel, $this> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(TeamModel::class);
    }
}
