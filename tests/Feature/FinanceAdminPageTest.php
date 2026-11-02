<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Models\FinancialMovementModel;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->user = User::factory()->create();
});

test('el acceso a finanzas requiere autenticación', function () {
    $response = $this->get(route('admin.finance.index'));

    $response->assertRedirect(route('login'));
});

test('responde 403 a usuarios sin permisos', function () {
    $this->actingAs($this->user)->get(route('admin.finance.index'))->assertForbidden();
    $this->actingAs($this->user)->post(route('admin.finance.store'), [
        'type' => 'income',
        'amount' => '10.00',
        'concept' => 'Donación',
        'category' => 'donations',
        'occurred_on' => '2027-03-15',
    ])->assertForbidden();
});

test('renderiza Admin/Finance con el contrato de claves del DTO', function () {
    createFinancialMovement([
        'type' => 'expense',
        'amount' => '100.07',
        'concept' => 'Hosting',
        'category' => 'hosting',
        'occurred_on' => date('Y').'-03-15',
        'created_by' => $this->admin->id,
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.finance.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Finance')
        ->has('summary', fn (Assert $summary) => $summary
            ->where('year', (int) date('Y'))
            ->where('period.from', date('Y').'-01-01')
            ->where('period.to', date('Y').'-12-31')
            ->where('income_total', '0.00')
            ->where('expense_total', '100.07')
            ->where('balance', '-100.07')
            ->has('by_category', 1)
            ->has('top_expenses', 1)
            ->where('movements_count', 1)
        )
        ->has('movements', fn (Assert $movements) => $movements
            ->where('per_page', 50)
            ->has('data', 1)
            ->has('data.0', fn (Assert $row) => $row
                ->has('id')
                ->has('type')
                ->has('type_label')
                ->has('amount')
                ->has('concept')
                ->has('category')
                ->has('category_label')
                ->has('occurred_on')
                ->has('source')
                ->has('source_label')
                ->has('status')
                ->has('status_label')
                ->has('notes')
                ->has('created_by')
                ->has('created_at')
                ->has('updated_at')
            )
            ->etc()
        )
        ->has('filters', fn (Assert $filters) => $filters
            ->where('year', (int) date('Y'))
            ->where('type', null)
            ->where('category', null)
            ->where('from', null)
            ->where('to', null)
            ->where('page', 1)
        )
        ->has('categories')
    );
});

test('el CRUD funciona para superadministradores con mensajes flash', function () {
    $created = $this->actingAs($this->admin)->post(route('admin.finance.store'), [
        'type' => 'expense',
        'amount' => '25.50',
        'concept' => 'Dominio anual',
        'category' => 'domains',
        'occurred_on' => '2027-03-15',
        'notes' => 'Factura 001',
    ]);

    $created->assertRedirect(route('admin.finance.index'));
    $created->assertInertiaFlash('success', 'Movimiento creado correctamente');

    $movement = FinancialMovementModel::query()->firstOrFail();

    $updated = $this->patch(route('admin.finance.update', $movement->id), [
        'type' => 'expense',
        'amount' => '30.00',
        'concept' => 'Dominio renovado',
        'category' => 'domains',
        'occurred_on' => '2027-04-01',
        'notes' => null,
    ]);

    $updated->assertRedirect(route('admin.finance.index'));
    $updated->assertInertiaFlash('success', 'Movimiento actualizado correctamente');
    expect((string) $movement->fresh()->amount)->toBe('30.00');

    $deleted = $this->delete(route('admin.finance.destroy', $movement->id));

    $deleted->assertRedirect(route('admin.finance.index'));
    $deleted->assertInertiaFlash('success', 'Movimiento eliminado correctamente');
    expect(FinancialMovementModel::query()->count())->toBe(0);
});

test('valida la unicidad de la combinación source y external_id', function () {
    createFinancialMovement(['source' => 'imported', 'external_id' => 'ext-1']);

    expect(fn () => createFinancialMovement(['source' => 'imported', 'external_id' => 'ext-1']))
        ->toThrow(QueryException::class);

    expect(fn () => createFinancialMovement(['source' => 'api', 'external_id' => 'ext-1']))
        ->not->toThrow(QueryException::class);
});

test('permite varios movimientos manuales con external_id nulo', function () {
    createFinancialMovement(['source' => 'manual', 'external_id' => null]);
    createFinancialMovement(['source' => 'manual', 'external_id' => null]);

    expect(FinancialMovementModel::query()->count())->toBe(2);
});

test('los agregados monetarios usan SQL compatible con MySQL y SQLite', function () {
    createFinancialMovement(['type' => 'income', 'amount' => '1234.56', 'occurred_on' => '2027-03-15']);
    createFinancialMovement(['type' => 'expense', 'amount' => '100.07', 'category' => 'hosting', 'occurred_on' => '2027-03-16']);

    // SUM(ROUND(amount * 100)) es válido en MySQL y SQLite y agrega en céntimos.
    $totals = DB::table('financial_movements')
        ->selectRaw('type, SUM(ROUND(amount * 100)) AS cents')
        ->groupBy('type')
        ->pluck('cents', 'type');

    expect((int) $totals['income'])->toBe(123456)
        ->and((int) $totals['expense'])->toBe(10007);
});

test('los movimientos de otros años no aparecen en el listado por defecto', function () {
    createFinancialMovement(['occurred_on' => '2020-03-15', 'concept' => 'Antiguo']);
    createFinancialMovement(['occurred_on' => date('Y').'-03-15', 'concept' => 'Actual']);

    $response = $this->actingAs($this->admin)->get(route('admin.finance.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Finance')
        ->has('movements.data', 1)
        ->where('movements.data.0.concept', 'Actual')
    );
});
