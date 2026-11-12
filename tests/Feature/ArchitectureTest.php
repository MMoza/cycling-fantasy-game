<?php

declare(strict_types=1);

/*
 * Reglas de arquitectura: la capa de Domain no conoce la persistencia ni las
 * capas superiores. El ámbito se ampliará a Application según avancen las
 * fases del desacoplamiento.
 */

arch('Domain no conoce la persistencia ni las capas superiores')
    ->expect('App\Domain')
    ->not->toUse('App\Infrastructure')
    ->not->toUse('App\Models')
    ->not->toUse('App\Application')
    ->not->toUse('Illuminate\Database')
    ->not->toUse('Illuminate\Support\Facades');

arch('Domain no usa utilidades de Laravel')
    ->expect('App\Domain')
    ->not->toUse('Illuminate\Support\Str')
    ->not->toUse('Illuminate\Support\Collection');

arch('el módulo Finance de Application no conoce la persistencia')
    ->expect('App\Application\UseCases\Finance')
    ->not->toUse('App\Infrastructure')
    ->not->toUse('Illuminate\Database')
    ->not->toUse('Illuminate\Support\Facades');

arch('los DTOs de Finance no conocen la persistencia')
    ->expect('App\Application\DTOs\Finance')
    ->not->toUse('App\Infrastructure')
    ->not->toUse('Illuminate\Database');
