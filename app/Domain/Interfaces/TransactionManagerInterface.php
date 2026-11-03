<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

/**
 * Abstracción de transacciones: Application orquesta unidades de trabajo sin
 * conocer la API de base de datos.
 */
interface TransactionManagerInterface
{
    /**
     * Ejecuta el callback dentro de una transacción: confirma si termina
     * correctamente y revierte si lanza una excepción.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function run(callable $callback): mixed;
}
