<?php

declare(strict_types=1);

use App\Application\UseCases\Finance\GetFinanceSummaryUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->useCase = new GetFinanceSummaryUseCase;
});

test('calcula el balance con decimales sin errores de coma flotante', function () {
    createFinancialMovement([
        'type' => 'income',
        'amount' => '1234.56',
        'category' => 'sponsorships',
        'occurred_on' => '2027-03-15',
    ]);
    createFinancialMovement([
        'type' => 'expense',
        'amount' => '100.07',
        'category' => 'hosting',
        'occurred_on' => '2027-04-01',
    ]);

    $summary = $this->useCase->execute(2027);

    expect($summary['income_total'])->toBe('1234.56')
        ->and($summary['expense_total'])->toBe('100.07')
        ->and($summary['balance'])->toBe('1134.49');
});

test('devuelve un resumen vacío cuando no hay movimientos', function () {
    $summary = $this->useCase->execute(2027);

    expect($summary['year'])->toBe(2027)
        ->and($summary['period'])->toBe(['from' => '2027-01-01', 'to' => '2027-12-31'])
        ->and($summary['income_total'])->toBe('0.00')
        ->and($summary['expense_total'])->toBe('0.00')
        ->and($summary['balance'])->toBe('0.00')
        ->and($summary['by_category'])->toBe([])
        ->and($summary['top_expenses'])->toBe([])
        ->and($summary['movements_count'])->toBe(0);
});

test('excluye los movimientos pendientes del balance confirmado', function () {
    createFinancialMovement([
        'type' => 'income',
        'amount' => '50.00',
        'category' => 'donations',
        'occurred_on' => '2027-03-15',
        'status' => 'confirmed',
    ]);
    createFinancialMovement([
        'type' => 'income',
        'amount' => '999.99',
        'category' => 'donations',
        'occurred_on' => '2027-03-16',
        'status' => 'pending',
    ]);
    createFinancialMovement([
        'type' => 'expense',
        'amount' => '500.00',
        'category' => 'marketing',
        'occurred_on' => '2027-03-17',
        'status' => 'pending',
    ]);

    $summary = $this->useCase->execute(2027);

    expect($summary['income_total'])->toBe('50.00')
        ->and($summary['expense_total'])->toBe('0.00')
        ->and($summary['balance'])->toBe('50.00')
        ->and($summary['by_category'])->toHaveCount(1)
        ->and($summary['movements_count'])->toBe(1);
});

test('agrega los totales por categoría', function () {
    createFinancialMovement(['type' => 'expense', 'amount' => '10.00', 'category' => 'hosting', 'occurred_on' => '2027-03-15']);
    createFinancialMovement(['type' => 'expense', 'amount' => '5.50', 'category' => 'hosting', 'occurred_on' => '2027-03-16']);
    createFinancialMovement(['type' => 'income', 'amount' => '20.00', 'category' => 'donations', 'occurred_on' => '2027-03-17']);

    $summary = $this->useCase->execute(2027);

    expect($summary['by_category'])->toHaveCount(2);

    $hosting = collect($summary['by_category'])->firstWhere('category', 'hosting');
    $donations = collect($summary['by_category'])->firstWhere('category', 'donations');

    expect($hosting)->toBe([
        'type' => 'expense',
        'category' => 'hosting',
        'label' => 'Infraestructura',
        'total' => '15.50',
        'count' => 2,
    ])->and($donations)->toBe([
        'type' => 'income',
        'category' => 'donations',
        'label' => 'Donaciones',
        'total' => '20.00',
        'count' => 1,
    ]);
});

test('ordena los cinco principales gastos por importe', function () {
    foreach ([10, 20, 30, 40, 50, 60, 70] as $index => $amount) {
        createFinancialMovement([
            'type' => 'expense',
            'amount' => sprintf('%d.00', $amount),
            'category' => 'marketing',
            'occurred_on' => sprintf('2027-03-%02d', $index + 1),
        ]);
    }

    createFinancialMovement([
        'type' => 'income',
        'amount' => '999.99',
        'category' => 'advertising',
        'occurred_on' => '2027-03-20',
    ]);

    $summary = $this->useCase->execute(2027);

    expect($summary['top_expenses'])->toHaveCount(5)
        ->and(array_column($summary['top_expenses'], 'amount'))->toBe([
            '70.00', '60.00', '50.00', '40.00', '30.00',
        ]);
});

test('solo contabiliza los movimientos del año consultado, incluidos los límites', function () {
    createFinancialMovement(['occurred_on' => '2027-01-01', 'amount' => '10.00']);
    createFinancialMovement(['occurred_on' => '2027-12-31', 'amount' => '20.00']);
    createFinancialMovement(['occurred_on' => '2026-12-31', 'amount' => '40.00']);
    createFinancialMovement(['occurred_on' => '2028-01-01', 'amount' => '80.00']);

    $summary = $this->useCase->execute(2027);

    expect($summary['income_total'])->toBe('30.00')
        ->and($summary['movements_count'])->toBe(2);
});

test('movements_count cuenta los movimientos confirmados del periodo', function () {
    createFinancialMovement(['type' => 'income', 'amount' => '10.00', 'occurred_on' => '2027-03-15']);
    createFinancialMovement(['type' => 'expense', 'amount' => '5.00', 'category' => 'hosting', 'occurred_on' => '2027-03-16']);
    createFinancialMovement(['type' => 'income', 'amount' => '5.00', 'occurred_on' => '2027-03-17', 'status' => 'pending']);

    $summary = $this->useCase->execute(2027);

    expect($summary['movements_count'])->toBe(2);
});
