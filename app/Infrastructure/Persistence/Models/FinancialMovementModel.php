<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Domain\Entities\FinancialMovement;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\MovementCategory;
use App\Domain\ValueObjects\MovementSource;
use App\Domain\ValueObjects\MovementStatus;
use App\Domain\ValueObjects\MovementType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Persistencia de movimientos financieros. La capa de modelo no incorpora
 * cálculos financieros ni reglas de negocio: corresponden al dominio y a
 * los casos de uso.
 */
class FinancialMovementModel extends Model
{
    protected $table = 'financial_movements';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'type',
        'amount',
        'concept',
        'category',
        'occurred_on',
        'source',
        'external_id',
        'status',
        'notes',
        'created_by',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $movement): void {
            if (empty($movement->id)) {
                $movement->id = Str::uuid()->toString();
            }
        });
    }

    protected $casts = [
        'type' => MovementType::class,
        'amount' => 'decimal:2',
        'category' => MovementCategory::class,
        'occurred_on' => 'date',
        'source' => MovementSource::class,
        'status' => MovementStatus::class,
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Mapea la persistencia hacia la entidad de dominio.
     */
    public function toEntity(): FinancialMovement
    {
        /** @var MovementType $type */
        $type = $this->type;
        /** @var MovementCategory $category */
        $category = $this->category;
        /** @var MovementSource $source */
        $source = $this->source;
        /** @var MovementStatus $status */
        $status = $this->status;

        return new FinancialMovement(
            id: $this->id,
            type: $type,
            amount: Money::fromDecimalString((string) $this->amount),
            concept: $this->concept,
            category: $category,
            occurredOn: $this->occurred_on->toImmutable(),
            source: $source,
            externalId: $this->external_id,
            status: $status,
            notes: $this->notes,
            createdBy: $this->created_by,
            createdAt: $this->created_at?->toImmutable(),
            updatedAt: $this->updated_at?->toImmutable(),
        );
    }
}
