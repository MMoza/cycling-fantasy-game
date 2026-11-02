<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

use App\Domain\Exceptions\AiProviderException;

interface AiPredictionProviderInterface
{
    /**
     * Propone un pronóstico de etapa a partir del contexto facilitado.
     *
     * @param  array{
     *     competition: string,
     *     edition_year: int,
     *     stage: array<string, mixed>,
     *     participants: array<int, array{ref: string, name: string, team: string|null}>,
     *     fields: array<int, string>
     * }  $context
     * @return array<string, string|null> mapa campo => ref del participante propuesto
     *
     * @throws AiProviderException cuando el proveedor no está disponible o la respuesta no es utilizable
     */
    public function proposeStagePrediction(array $context): array;
}
