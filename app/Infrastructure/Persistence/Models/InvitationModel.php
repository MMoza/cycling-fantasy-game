<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Domain\Entities\Invitation;
use App\Domain\ValueObjects\InvitationCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationModel extends Model
{
    protected $table = 'invitations';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'code',
        'accepted_count',
    ];

    protected $casts = [
        'accepted_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getCodeAttribute(): InvitationCode
    {
        return InvitationCode::fromString($this->attributes['code']);
    }

    public function setCodeAttribute(InvitationCode|string $value): void
    {
        $this->attributes['code'] = $value instanceof InvitationCode ? $value->value : $value;
    }

    public function toDomain(): Invitation
    {
        return Invitation::create(
            id: $this->id,
            userId: $this->user_id,
            code: $this->code,
        );
    }
}
