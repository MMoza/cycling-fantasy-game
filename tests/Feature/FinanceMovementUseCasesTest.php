<?php

declare(strict_types=1);

use App\Application\DTOs\Finance\StoreMovementDTO;
use App\Application\DTOs\Finance\UpdateMovementDTO;
use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\Finance\CreateMovementUseCase;
use App\Application\UseCases\Finance\DeleteMovementUseCase;
use App\Application\UseCases\Finance\UpdateMovementUseCase;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\MovementCategory;
use App\Domain\ValueObjects\MovementSource;
use App\Domain\ValueObjects\MovementStatus;
use App\Domain\ValueObjects\MovementType;
use App\Infrastructure\Persistence\Models\FinancialMovementModel;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('crea un movimiento manual confirmado con el usuario creador', function () {
    $useCase = app(CreateMovementUseCase::class);

    $movement = $useCase->execute(new StoreMovementDTO(
        type: MovementType::Income,
        amount: '100.50',
        concept: 'Donación anual',
        category: MovementCategory::Donations,
        occurredOn: new DateTimeImmutable('2027-03-15'),
        notes: 'Transferencia bancaria',
        createdBy: $this->user->id,
    ));

    expect($movement->id)->toBeUuid()
        ->and($movement->type)->toBe(MovementType::Income)
        ->and($movement->amount)->toBeInstanceOf(Money::class)
        ->and($movement->amount->toDecimalString())->toBe('100.50')
        ->and($movement->source)->toBe(MovementSource::Manual)
        ->and($movement->status)->toBe(MovementStatus::Confirmed)
        ->and($movement->externalId)->toBeNull()
        ->and($movement->createdBy)->toBe($this->user->id)
        ->and($movement->occurredOn->format('Y-m-d'))->toBe('2027-03-15');

    $persisted = FinancialMovementModel::find($movement->id);

    expect($persisted)->not->toBeNull()
        ->and((string) $persisted->amount)->toBe('100.50');
});

test('normaliza el importe a dos decimales', function () {
    $useCase = app(CreateMovementUseCase::class);

    $movement = $useCase->execute(new StoreMovementDTO(
        type: MovementType::Expense,
        amount: '5.5',
        concept: 'Dominio',
        category: MovementCategory::Domains,
        occurredOn: new DateTimeImmutable('2027-03-15'),
        notes: null,
        createdBy: $this->user->id,
    ));

    expect($movement->amount->toDecimalString())->toBe('5.50')
        ->and((string) FinancialMovementModel::find($movement->id)->amount)->toBe('5.50');
});

test('rechaza importes negativos', function () {
    $useCase = app(CreateMovementUseCase::class);

    expect(fn () => $useCase->execute(new StoreMovementDTO(
        type: MovementType::Expense,
        amount: '-10.00',
        concept: 'Reembolso',
        category: MovementCategory::Other,
        occurredOn: new DateTimeImmutable('2027-03-15'),
        notes: null,
        createdBy: $this->user->id,
    )))->toThrow(ApplicationException::class, 'no puede ser negativo');
});

test('rechaza importes con un formato no válido', function () {
    $useCase = app(CreateMovementUseCase::class);

    expect(fn () => $useCase->execute(new StoreMovementDTO(
        type: MovementType::Income,
        amount: 'no-es-un-importe',
        concept: 'Donación',
        category: MovementCategory::Donations,
        occurredOn: new DateTimeImmutable('2027-03-15'),
        notes: null,
        createdBy: $this->user->id,
    )))->toThrow(ApplicationException::class, 'no es válido');
});

test('rechaza categorías incompatibles con el tipo de movimiento', function () {
    $useCase = app(CreateMovementUseCase::class);

    expect(fn () => $useCase->execute(new StoreMovementDTO(
        type: MovementType::Income,
        amount: '10.00',
        concept: 'Donación',
        category: MovementCategory::Hosting,
        occurredOn: new DateTimeImmutable('2027-03-15'),
        notes: null,
        createdBy: $this->user->id,
    )))->toThrow(ApplicationException::class, 'no es compatible');
});

test('actualiza los campos editables de un movimiento', function () {
    $movement = createFinancialMovement(['created_by' => $this->user->id]);
    $useCase = app(UpdateMovementUseCase::class);

    $updated = $useCase->execute(new UpdateMovementDTO(
        id: $movement->id,
        type: MovementType::Expense,
        amount: '80.25',
        concept: 'Hosting renovado',
        category: MovementCategory::Hosting,
        occurredOn: new DateTimeImmutable('2027-06-01'),
        notes: 'Factura 2027-06',
    ));

    expect($updated->type)->toBe(MovementType::Expense)
        ->and($updated->amount->toDecimalString())->toBe('80.25')
        ->and($updated->concept)->toBe('Hosting renovado')
        ->and($updated->category)->toBe(MovementCategory::Hosting)
        ->and($updated->occurredOn->format('Y-m-d'))->toBe('2027-06-01')
        ->and($updated->notes)->toBe('Factura 2027-06');

    expect((string) FinancialMovementModel::find($movement->id)->amount)->toBe('80.25');
});

test('mantiene la identidad, el origen y el estado del movimiento al actualizarlo', function () {
    $movement = createFinancialMovement([
        'source' => 'imported',
        'external_id' => 'ext-1',
        'status' => 'pending',
        'created_by' => $this->user->id,
    ]);
    $useCase = app(UpdateMovementUseCase::class);

    $updated = $useCase->execute(new UpdateMovementDTO(
        id: $movement->id,
        type: MovementType::Income,
        amount: '10.00',
        concept: 'Concepto nuevo',
        category: MovementCategory::Other,
        occurredOn: new DateTimeImmutable('2027-06-01'),
        notes: null,
    ));

    expect($updated->id)->toBe($movement->id)
        ->and($updated->source)->toBe(MovementSource::Imported)
        ->and($updated->externalId)->toBe('ext-1')
        ->and($updated->status)->toBe(MovementStatus::Pending)
        ->and($updated->createdBy)->toBe($this->user->id);
});

test('rechaza actualizar un movimiento con una categoría incompatible', function () {
    $movement = createFinancialMovement();
    $useCase = app(UpdateMovementUseCase::class);

    expect(fn () => $useCase->execute(new UpdateMovementDTO(
        id: $movement->id,
        type: MovementType::Income,
        amount: '10.00',
        concept: 'Concepto',
        category: MovementCategory::Marketing,
        occurredOn: new DateTimeImmutable('2027-06-01'),
        notes: null,
    )))->toThrow(ApplicationException::class, 'no es compatible');

    expect(FinancialMovementModel::find($movement->id)->concept)->toBe('Movimiento de prueba');
});

test('rechaza actualizar un movimiento inexistente', function () {
    $useCase = app(UpdateMovementUseCase::class);

    expect(fn () => $useCase->execute(new UpdateMovementDTO(
        id: '00000000-0000-0000-0000-000000000000',
        type: MovementType::Income,
        amount: '10.00',
        concept: 'Concepto',
        category: MovementCategory::Other,
        occurredOn: new DateTimeImmutable('2027-06-01'),
        notes: null,
    )))->toThrow(ApplicationException::class, 'no existe');
});

test('elimina el movimiento solicitado', function () {
    $movement = createFinancialMovement();
    $admin = User::factory()->create(['is_admin' => true]);
    $useCase = app(DeleteMovementUseCase::class);

    $useCase->execute($movement->id, $admin->id);

    expect(FinancialMovementModel::find($movement->id))->toBeNull();
});

test('impide eliminar movimientos sin autorización', function () {
    $movement = createFinancialMovement();
    $useCase = app(DeleteMovementUseCase::class);

    expect(fn () => $useCase->execute($movement->id, $this->user->id))
        ->toThrow(ApplicationException::class, 'No tienes permisos');

    expect(FinancialMovementModel::find($movement->id))->not->toBeNull();
});

test('rechaza eliminar un movimiento inexistente', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $useCase = app(DeleteMovementUseCase::class);

    expect(fn () => $useCase->execute('00000000-0000-0000-0000-000000000000', $admin->id))
        ->toThrow(ApplicationException::class, 'no existe');
});
