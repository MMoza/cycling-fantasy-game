<?php

declare(strict_types=1);

use App\Domain\Interfaces\TransactionManagerInterface;
use App\Infrastructure\Persistence\Models\CountryModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ejecuta el callback y devuelve su resultado', function () {
    $manager = app(TransactionManagerInterface::class);

    $result = $manager->run(fn (): string => 'ok');

    expect($result)->toBe('ok');
});

test('confirma los cambios cuando el callback termina correctamente', function () {
    $manager = app(TransactionManagerInterface::class);

    $manager->run(function (): void {
        CountryModel::create(['id' => 'XX', 'name' => 'Testland']);
    });

    expect(CountryModel::find('XX'))->not->toBeNull();
});

test('revierte los cambios cuando el callback lanza una excepción', function () {
    $manager = app(TransactionManagerInterface::class);

    expect(function () use ($manager): void {
        $manager->run(function (): void {
            CountryModel::create(['id' => 'YY', 'name' => 'Rollbackland']);

            throw new RuntimeException('boom');
        });
    })->toThrow(RuntimeException::class);

    expect(CountryModel::find('YY'))->toBeNull();
});
