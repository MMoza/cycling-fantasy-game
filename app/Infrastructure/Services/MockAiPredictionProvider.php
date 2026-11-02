<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Domain\Interfaces\AiPredictionProviderInterface;

/**
 * Proveedor de IA de prueba para local (GEMINI_API_KEY=mock).
 *
 * Devuelve una propuesta determinista con los primeros participantes de la
 * lista para poder probar el flujo completo del botón «Hacer pronóstico con IA»
 * sin llamar a ningún proveedor externo.
 */
class MockAiPredictionProvider implements AiPredictionProviderInterface
{
    public function proposeStagePrediction(array $context): array
    {
        $refs = array_values(array_column($context['participants'], 'ref'));

        $fallbacks = [
            'stage_winner' => $refs[0] ?? null,
            'stage_second' => $refs[1] ?? $refs[0] ?? null,
            'stage_third' => $refs[2] ?? $refs[0] ?? null,
            'stage_leader' => $refs[3] ?? $refs[0] ?? null,
            'stage_combativo' => $refs[4] ?? $refs[0] ?? null,
        ];

        $proposal = [];

        foreach ($context['fields'] as $field) {
            $proposal[$field] = $fallbacks[$field] ?? null;
        }

        return $proposal;
    }
}
