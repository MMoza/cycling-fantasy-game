<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Models\FinancialMovementModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

function financeMovementPayload(array $overrides = []): array
{
    return array_merge([
        'type' => 'expense',
        'amount' => '25.50',
        'concept' => 'Dominio anual',
        'category' => 'domains',
        'occurred_on' => '2027-03-15',
        'notes' => null,
    ], $overrides);
}

test('acepta un movimiento válido', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.finance.store'), financeMovementPayload());

    $response->assertRedirect(route('admin.finance.index'));
    expect(FinancialMovementModel::query()->count())->toBe(1);
});

test('rechaza importes negativos', function () {
    $response = $this->actingAs($this->admin)->post(
        route('admin.finance.store'),
        financeMovementPayload(['amount' => '-25.50']),
    );

    $response->assertSessionHasErrors('amount');
    expect(FinancialMovementModel::query()->count())->toBe(0);
});

test('rechaza importes con más de dos decimales', function () {
    $response = $this->actingAs($this->admin)->post(
        route('admin.finance.store'),
        financeMovementPayload(['amount' => '25.505']),
    );

    $response->assertSessionHasErrors('amount');
});

test('rechaza categorías inexistentes', function () {
    $response = $this->actingAs($this->admin)->post(
        route('admin.finance.store'),
        financeMovementPayload(['category' => 'categoria_inexistente']),
    );

    $response->assertSessionHasErrors('category');
});

test('rechaza categorías incompatibles con el tipo de movimiento', function () {
    $response = $this->actingAs($this->admin)->post(
        route('admin.finance.store'),
        financeMovementPayload(['type' => 'income', 'category' => 'domains']),
    );

    $response->assertSessionHasErrors('category');
});

test('rechaza tipos de movimiento inválidos', function () {
    $response = $this->actingAs($this->admin)->post(
        route('admin.finance.store'),
        financeMovementPayload(['type' => 'reembolso']),
    );

    $response->assertSessionHasErrors('type');
});

test('rechaza fechas inválidas', function () {
    $response = $this->actingAs($this->admin)->post(
        route('admin.finance.store'),
        financeMovementPayload(['occurred_on' => 'ayer']),
    );

    $response->assertSessionHasErrors('occurred_on');
});

test('rechaza conceptos que superan la longitud máxima', function () {
    $response = $this->actingAs($this->admin)->post(
        route('admin.finance.store'),
        financeMovementPayload(['concept' => str_repeat('a', 161)]),
    );

    $response->assertSessionHasErrors('concept');
});

test('aplica las mismas validaciones al actualizar un movimiento', function () {
    $movement = createFinancialMovement();

    $response = $this->actingAs($this->admin)->patch(
        route('admin.finance.update', $movement->id),
        financeMovementPayload(['amount' => '-1.00']),
    );

    $response->assertSessionHasErrors('amount');
});
