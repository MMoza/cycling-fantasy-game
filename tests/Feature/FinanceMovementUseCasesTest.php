<?php

declare(strict_types=1);

use App\Application\DTOs\Finance\StoreMovementDTO;
use App\Application\DTOs\Finance\UpdateMovementDTO;
use App\Application\Exceptions\ApplicationException;
use App\Application\Services\MovementValidationService;
use App\Application\UseCases\Finance\CreateMovementUseCase;
use App\Application\UseCases\Finance\DeleteMovementUseCase;
use App\Application\UseCases\Finance\UpdateMovementUseCase;
use App\Domain\ValueObjects\MovementCategory;
use App\Domain\ValueObjects\MovementType;
use App\Infrastructure\Persistence\Models\FinancialMovementModel;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->validation = new MovementValidationService;
    $this->user = User::factory()->create();
});

test('crea un movimiento manual confirmado con el usuario creador', function () {
    $useCase = new CreateMovementUseCase($this->validation);

    $movement = $useCase->execute(new StoreMovementDTO(
        type: MovementType::Income,
        amount: '100.50',
        concept: 'Donación anual',
        category: MovementCategory::Donations,
        occurredOn: CarbonImmutable::parse('2027-03-15'),
        notes: 'Transferencia bancaria',
        createdBy: $this->user->id,
    ));

    expect($movement->id)->toBeUuid()
        ->and($movement->type)->toBe(MovementType::Income)
        ->and((string) $movement->amount)->toBe('100.50')
        ->and($movement->source->value)->toBe('manual')
        ->and($movement->status->value)->toBe('confirmed')
        ->and($movement->external_id)->toBeNull()
        ->and($movement->created_by)->toBe($this->user->id)
        ->and($movement->occurred_on->format('Y-m-d'))->toBe('2027-03-15');
});

test('normaliza el importe a dos decimales', function () {
    $useCase = new CreateMovementUseCase($this->validation);

    $movement = $useCase->execute(new StoreMovementDTO(
        type: MovementType::Expense,
        amount: '5.5',
        concept: 'Dominio',
        category: MovementCategory::Domains,
        occurredOn: CarbonImmutable::parse('2027-03-15'),
        notes: null,
        createdBy: $this->user->id,
    ));

    expect((string) $movement->amount)->toBe('5.50');
});

test('rechaza importes negativos', function () {
    $useCase = new CreateMovementUseCase($this->validation);

    expect(fn () => $useCase->execute(new StoreMovementDTO(
        type: MovementType::Expense,
        amount: '-10.00',
        concept: 'Reembolso',
        category: MovementCategory::Other,
        occurredOn: CarbonImmutable::parse('2027-03-15'),
        notes: null,
        createdBy: $this->user->id,
    )))->toThrow(ApplicationException::class, 'no puede ser negativo');
});

test('rechaza importes con un formato no válido', function () {
    $useCase = new CreateMovementUseCase($this->validation);

    expect(fn () => $useCase->execute(new StoreMovementDTO(
        type: MovementType::Income,
        amount: 'no-es-un-importe',
        concept: 'Donación',
        category: MovementCategory::Donations,
        occurredOn: CarbonImmutable::parse('2027-03-15'),
        notes: null,
        createdBy: $this->user->id,
    )))->toThrow(ApplicationException::class, 'no es válido');
});

test('rechaza categorías incompatibles con el tipo de movimiento', function () {
    $useCase = new CreateMovementUseCase($this->validation);

    expect(fn () => $useCase->execute(new StoreMovementDTO(
        type: MovementType::Income,
        amount: '10.00',
        concept: 'Donación',
        category: MovementCategory::Hosting,
        occurredOn: CarbonImmutable::parse('2027-03-15'),
        notes: null,
        createdBy: $this->user->id,
    )))->toThrow(ApplicationException::class, 'no es compatible');
});

test('actualiza los campos editables de un movimiento', function () {
    $movement = createFinancialMovement(['created_by' => $this->user->id]);
    $useCase = new UpdateMovementUseCase($this->validation);

    $updated = $useCase->execute(new UpdateMovementDTO(
        id: $movement->id,
        type: MovementType::Expense,
        amount: '80.25',
        concept: 'Hosting renovado',
        category: MovementCategory::Hosting,
        occurredOn: CarbonImmutable::parse('2027-06-01'),
        notes: 'Factura 2027-06',
    ));

    expect($updated->type)->toBe(MovementType::Expense)
        ->and((string) $updated->amount)->toBe('80.25')
        ->and($updated->concept)->toBe('Hosting renovado')
        ->and($updated->category)->toBe(MovementCategory::Hosting)
        ->and($updated->occurred_on->format('Y-m-d'))->toBe('2027-06-01')
        ->and($updated->notes)->toBe('Factura 2027-06');
});

test('mantiene la identidad, el origen y el estado del movimiento al actualizarlo', function () {
    $movement = createFinancialMovement([
        'source' => 'imported',
        'external_id' => 'ext-1',
        'status' => 'pending',
        'created_by' => $this->user->id,
    ]);
    $useCase = new UpdateMovementUseCase($this->validation);

    $updated = $useCase->execute(new UpdateMovementDTO(
        id: $movement->id,
        type: MovementType::Income,
        amount: '10.00',
        concept: 'Concepto nuevo',
        category: MovementCategory::Other,
        occurredOn: CarbonImmutable::parse('2027-06-01'),
        notes: null,
    ));

    expect($updated->id)->toBe($movement->id)
        ->and($updated->source->value)->toBe('imported')
        ->and($updated->external_id)->toBe('ext-1')
        ->and($updated->status->value)->toBe('pending')
        ->and($updated->created_by)->toBe($this->user->id);
});

test('rechaza actualizar un movimiento con una categoría incompatible', function () {
    $movement = createFinancialMovement();
    $useCase = new UpdateMovementUseCase($this->validation);

    expect(fn () => $useCase->execute(new UpdateMovementDTO(
        id: $movement->id,
        type: MovementType::Income,
        amount: '10.00',
        concept: 'Concepto',
        category: MovementCategory::Marketing,
        occurredOn: CarbonImmutable::parse('2027-06-01'),
        notes: null,
    )))->toThrow(ApplicationException::class, 'no es compatible');

    expect(FinancialMovementModel::find($movement->id)->concept)->toBe('Movimiento de prueba');
});

test('elimina el movimiento solicitado', function () {
    $movement = createFinancialMovement();
    $admin = User::factory()->create(['is_admin' => true]);
    $useCase = new DeleteMovementUseCase;

    $useCase->execute($movement->id, $admin);

    expect(FinancialMovementModel::find($movement->id))->toBeNull();
});

test('impide eliminar movimientos sin autorización', function () {
    $movement = createFinancialMovement();
    $useCase = new DeleteMovementUseCase;

    expect(fn () => $useCase->execute($movement->id, $this->user))
        ->toThrow(ApplicationException::class, 'No tienes permisos');

    expect(FinancialMovementModel::find($movement->id))->not->toBeNull();
});
