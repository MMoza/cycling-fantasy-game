<?php

declare(strict_types=1);

use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\Finance\ListMovementsUseCase;
use App\Domain\ValueObjects\MovementCategory;
use App\Domain\ValueObjects\MovementFilters;
use App\Domain\ValueObjects\MovementType;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->useCase = app(ListMovementsUseCase::class);
});

test('filtra los movimientos por año', function () {
    createFinancialMovement(['occurred_on' => '2027-03-15', 'concept' => 'Dentro']);
    createFinancialMovement(['occurred_on' => '2026-03-15', 'concept' => 'Fuera']);

    $movements = $this->useCase->execute(new MovementFilters(2027, null, null, null, null, 1))->movements;

    expect($movements['data'])->toHaveCount(1)
        ->and($movements['data'][0]['concept'])->toBe('Dentro')
        ->and($movements['total'])->toBe(1);
});

test('filtra los movimientos por tipo', function () {
    createFinancialMovement(['type' => 'income', 'category' => 'donations', 'occurred_on' => '2027-03-15']);
    createFinancialMovement(['type' => 'expense', 'category' => 'hosting', 'occurred_on' => '2027-03-16']);

    $movements = $this->useCase->execute(new MovementFilters(2027, MovementType::Expense, null, null, null, 1))->movements;

    expect($movements['data'])->toHaveCount(1)
        ->and($movements['data'][0]['type'])->toBe('expense');
});

test('filtra los movimientos por categoría', function () {
    createFinancialMovement(['type' => 'expense', 'category' => 'hosting', 'occurred_on' => '2027-03-15']);
    createFinancialMovement(['type' => 'expense', 'category' => 'marketing', 'occurred_on' => '2027-03-16']);

    $movements = $this->useCase->execute(new MovementFilters(2027, null, MovementCategory::Marketing, null, null, 1))->movements;

    expect($movements['data'])->toHaveCount(1)
        ->and($movements['data'][0]['category'])->toBe('marketing');
});

test('filtra los movimientos por rango de fechas', function () {
    createFinancialMovement(['occurred_on' => '2027-03-10', 'concept' => 'Antes']);
    createFinancialMovement(['occurred_on' => '2027-03-15', 'concept' => 'Dentro']);
    createFinancialMovement(['occurred_on' => '2027-03-20', 'concept' => 'Después']);

    $movements = $this->useCase->execute(new MovementFilters(
        2027,
        null,
        null,
        new DateTimeImmutable('2027-03-14'),
        new DateTimeImmutable('2027-03-16'),
        1,
    ))->movements;

    expect($movements['data'])->toHaveCount(1)
        ->and($movements['data'][0]['concept'])->toBe('Dentro');
});

test('combina los filtros de tipo, categoría y rango de fechas', function () {
    createFinancialMovement(['type' => 'expense', 'category' => 'marketing', 'amount' => '10.00', 'occurred_on' => '2027-03-15', 'concept' => 'Coincide']);
    createFinancialMovement(['type' => 'expense', 'category' => 'marketing', 'amount' => '10.00', 'occurred_on' => '2027-05-15', 'concept' => 'Fuera de rango']);
    createFinancialMovement(['type' => 'expense', 'category' => 'hosting', 'amount' => '10.00', 'occurred_on' => '2027-03-15', 'concept' => 'Otra categoría']);
    createFinancialMovement(['type' => 'income', 'category' => 'marketing', 'amount' => '10.00', 'occurred_on' => '2027-03-15', 'concept' => 'Otro tipo']);

    $movements = $this->useCase->execute(new MovementFilters(
        2027,
        MovementType::Expense,
        MovementCategory::Marketing,
        new DateTimeImmutable('2027-03-01'),
        new DateTimeImmutable('2027-03-31'),
        1,
    ))->movements;

    expect($movements['data'])->toHaveCount(1)
        ->and($movements['data'][0]['concept'])->toBe('Coincide');
});

test('ordena los movimientos por fecha descendente', function () {
    createFinancialMovement(['occurred_on' => '2027-03-15', 'concept' => 'Primero']);
    createFinancialMovement(['occurred_on' => '2027-03-17', 'concept' => 'Tercero']);
    createFinancialMovement(['occurred_on' => '2027-03-16', 'concept' => 'Segundo']);

    $movements = $this->useCase->execute(new MovementFilters(2027, null, null, null, null, 1))->movements;

    expect(array_column($movements['data'], 'concept'))->toBe(['Tercero', 'Segundo', 'Primero']);
});

test('pagina los movimientos de 50 en 50', function () {
    foreach (range(1, 55) as $day) {
        createFinancialMovement([
            'occurred_on' => sprintf('2027-03-%02d', min($day, 31)),
            'concept' => sprintf('Movimiento %02d', $day),
        ]);
    }

    $firstPage = $this->useCase->execute(new MovementFilters(2027, null, null, null, null, 1))->movements;

    expect($firstPage['data'])->toHaveCount(50)
        ->and($firstPage['per_page'])->toBe(50)
        ->and($firstPage['total'])->toBe(55)
        ->and($firstPage['last_page'])->toBe(2);

    $secondPage = $this->useCase->execute(new MovementFilters(2027, null, null, null, null, 2))->movements;

    expect($secondPage['data'])->toHaveCount(5)
        ->and($secondPage['current_page'])->toBe(2);
});

test('rechaza un rango de fechas inválido', function () {
    expect(fn () => $this->useCase->execute(new MovementFilters(
        2027,
        null,
        null,
        new DateTimeImmutable('2027-05-01'),
        new DateTimeImmutable('2027-01-01'),
        1,
    )))->toThrow(ApplicationException::class, 'El rango de fechas no es válido');
});

test('el listado incluye movimientos pendientes y confirmados', function () {
    createFinancialMovement(['occurred_on' => '2027-03-15', 'status' => 'confirmed']);
    createFinancialMovement(['occurred_on' => '2027-03-16', 'status' => 'pending']);

    $movements = $this->useCase->execute(new MovementFilters(2027, null, null, null, null, 1))->movements;

    expect($movements['data'])->toHaveCount(2);
});
