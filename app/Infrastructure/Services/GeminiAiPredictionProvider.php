<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Domain\Exceptions\AiProviderException;
use App\Domain\Interfaces\AiPredictionProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAiPredictionProvider implements AiPredictionProviderInterface
{
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    private const FIELD_DESCRIPTIONS = [
        'stage_winner' => 'ganador de la etapa',
        'stage_second' => 'segundo clasificado de la etapa',
        'stage_third' => 'tercer clasificado de la etapa',
        'stage_leader' => 'líder de la clasificación general tras la etapa',
        'stage_combativo' => 'corredor combativo del día',
    ];

    public function proposeStagePrediction(array $context): array
    {
        $apiKey = (string) config('services.gemini.api_key');

        if ($apiKey === '') {
            throw new AiProviderException('El asistente de IA no está configurado. Haz tu pronóstico manualmente.');
        }

        $model = (string) config('services.gemini.model', 'gemini-3.6-flash');

        try {
            $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
                ->acceptJson()
                ->timeout(30)
                ->post(sprintf(self::ENDPOINT, $model), [
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [['text' => $this->buildPrompt($context)]],
                    ]],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature' => 0.4,
                        'maxOutputTokens' => 1024,
                    ],
                ]);
        } catch (ConnectionException $e) {
            throw new AiProviderException(
                'No se pudo conectar con el asistente de IA. Inténtalo más tarde o haz tu pronóstico manualmente.',
                previous: $e,
            );
        }

        if ($response->status() === 429) {
            throw new AiProviderException('El asistente de IA está saturado. Inténtalo en unos minutos o haz tu pronóstico manualmente.');
        }

        if ($response->failed()) {
            Log::warning('[AI] Gemini request failed', [
                'status' => $response->status(),
                'body' => mb_substr($response->body(), 0, 500),
            ]);

            throw new AiProviderException('El asistente de IA no está disponible en este momento. Haz tu pronóstico manualmente.');
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw new AiProviderException('El asistente de IA devolvió una respuesta vacía. Inténtalo de nuevo.');
        }

        $decoded = json_decode($this->stripCodeFences($text), true);

        if (! is_array($decoded)) {
            throw new AiProviderException('El asistente de IA devolvió una respuesta no válida. Inténtalo de nuevo.');
        }

        $proposal = [];

        foreach ($context['fields'] as $field) {
            $value = $decoded[$field] ?? null;
            $proposal[$field] = is_string($value) ? $this->normalizeRef($value) : null;
        }

        return $proposal;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildPrompt(array $context): string
    {
        $stage = $context['stage'];

        $lines = [
            'Eres un experto en ciclismo de élite. Debes pronosticar el resultado de una etapa.',
            '',
            sprintf('Competición: %s %d', $context['competition'], $context['edition_year']),
            sprintf('Etapa %d: %s', $stage['number'], $stage['name']),
            sprintf(
                'Tipo: %s | Distancia: %s km | Desnivel: %s m | Dificultad: %s/5 | Recorrido: %s → %s | Fecha: %s',
                $stage['type'] ?? '-',
                $stage['distance_km'] ?? '-',
                $stage['elevation_gain_m'] ?? '-',
                $stage['difficulty'] ?? '-',
                $stage['origin'] ?? '-',
                $stage['destination'] ?? '-',
                $stage['date'] ?? '-',
            ),
            '',
            'Participantes (usa exactamente estos identificadores):',
        ];

        foreach ($context['participants'] as $participant) {
            $lines[] = sprintf(
                '%s — %s (%s)',
                $participant['ref'],
                $participant['name'],
                $participant['team'] ?? 'sin equipo',
            );
        }

        $jsonFields = [];

        foreach ($context['fields'] as $field) {
            $jsonFields[] = sprintf('  "%s": "R<n>",  // %s', $field, self::FIELD_DESCRIPTIONS[$field] ?? $field);
        }

        $lines[] = '';
        $lines[] = 'Devuelve SOLO un objeto JSON válido, sin texto adicional, con esta estructura exacta:';
        $lines[] = '{';
        $lines = [...$lines, ...$jsonFields];
        $lines[] = '}';
        $lines[] = '';
        $lines[] = 'Reglas:';
        $lines[] = '- Usa únicamente identificadores (R1, R2, ...) de la lista de participantes.';
        $lines[] = '- El ganador, el segundo y el tercer clasificado deben ser corredores distintos entre sí.';
        $lines[] = '- No inventes identificadores ni uses corredores que no participen en la etapa.';

        return implode("\n", $lines);
    }

    private function stripCodeFences(string $text): string
    {
        $text = trim($text);

        if (str_starts_with($text, '```')) {
            $text = (string) preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $text);
        }

        return $text;
    }

    private function normalizeRef(string $value): string
    {
        $value = trim($value);

        if (preg_match('/^R?(\d+)$/', $value, $matches) === 1) {
            return 'R'.(int) $matches[1];
        }

        return $value;
    }
}
