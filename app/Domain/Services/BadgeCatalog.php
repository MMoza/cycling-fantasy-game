<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Entities\BadgeDefinition;
use App\Domain\ValueObjects\BadgeCode;
use App\Domain\ValueObjects\BadgeDisplay;
use App\Domain\ValueObjects\BadgeMode;

/**
 * Catálogo de badges del juego. Añadir un badge = caso en BadgeCode,
 * definición aquí y su SVG en public/badges/{code}.svg.
 */
class BadgeCatalog
{
    /**
     * @return array<string, BadgeDefinition>
     */
    public static function definitions(): array
    {
        $definitions = [];

        foreach (self::DEFINITIONS as [$code, $name, $description, $mode, $levels, $category, $special, $display]) {
            $definition = new BadgeDefinition(
                code: $code,
                name: $name,
                description: $description,
                mode: $mode,
                levels: $levels,
                category: $category,
                special: $special,
                display: $display,
            );

            $definitions[$code->value] = $definition;
        }

        return $definitions;
    }

    public static function get(BadgeCode $code): BadgeDefinition
    {
        $definition = self::definitions()[$code->value] ?? null;

        if ($definition === null) {
            throw new \InvalidArgumentException("Badge sin definición: {$code->value}");
        }

        return $definition;
    }

    /**
     * @var array<int, array{BadgeCode, string, string, BadgeMode, array<int, int>, string, bool, BadgeDisplay}>
     */
    private const DEFINITIONS = [
        // ── Etapas ────────────────────────────────────────────────
        [BadgeCode::StagesPlayed, 'Etapas disputadas', 'Etapa disputada con al menos un pronóstico tuyo.', BadgeMode::Cumulative, [1, 5, 25, 100, 500], 'etapas', false, BadgeDisplay::Level],
        [BadgeCode::StagesWon, 'Etapas ganadas', 'Acertaste al ganador de la etapa.', BadgeMode::Cumulative, [1, 3, 10, 25, 100], 'etapas', false, BadgeDisplay::Level],

        // ── Participación ─────────────────────────────────────────
        [BadgeCode::GrandTourParticipation, 'Grandes Vueltas', 'Participaste en Grandes Vueltas (Tour, Giro o Vuelta).', BadgeMode::Cumulative, [1, 3, 10], 'participacion', false, BadgeDisplay::Level],
        [BadgeCode::MonumentParticipation, 'Monumentos', 'Participaste en carreras Monumento.', BadgeMode::Cumulative, [1, 5, 15], 'participacion', false, BadgeDisplay::Level],
        [BadgeCode::ChampionshipParticipation, 'Campeonatos', 'Participaste en campeonatos (Mundial o Europeo).', BadgeMode::Cumulative, [1, 3, 10], 'participacion', false, BadgeDisplay::Level],
        [BadgeCode::ClassicParticipation, 'Clásicas de 1 día', 'Participaste en clásicas de un día.', BadgeMode::Cumulative, [1, 5, 25], 'participacion', false, BadgeDisplay::Level],

        // ── Victorias ─────────────────────────────────────────────
        [BadgeCode::TourWinner, 'Ganador del Tour', 'Acertaste al ganador de la clasificación general del Tour.', BadgeMode::Cumulative, [1, 2, 3, 4, 5], 'victorias', false, BadgeDisplay::Times],
        [BadgeCode::GiroWinner, 'Ganador del Giro', 'Acertaste al ganador de la clasificación general del Giro.', BadgeMode::Cumulative, [1, 2, 3, 4, 5], 'victorias', false, BadgeDisplay::Times],
        [BadgeCode::VueltaWinner, 'Ganador de la Vuelta', 'Acertaste al ganador de la clasificación general de la Vuelta.', BadgeMode::Cumulative, [1, 2, 3, 4, 5], 'victorias', false, BadgeDisplay::Times],
        [BadgeCode::MonumentWinner, 'Ganador de monumento', 'Acertaste al ganador de una carrera Monumento.', BadgeMode::Cumulative, [1, 2, 3, 4, 5], 'victorias', false, BadgeDisplay::Times],
        [BadgeCode::WorldChampion, 'Campeón del mundo', 'Acertaste al ganador del Mundial en ruta.', BadgeMode::Once, [1], 'victorias', false, BadgeDisplay::None],

        // ── Hitos ─────────────────────────────────────────────────
        [BadgeCode::Trilogy, 'Trilogía', 'Ganaste las tres Grandes Vueltas: Tour, Giro y Vuelta.', BadgeMode::Once, [3], 'hitos', false, BadgeDisplay::None],
        [BadgeCode::MonumentCollection, 'Colección de monumentos', 'Ganaste las cinco carreras Monumento.', BadgeMode::Once, [5], 'hitos', false, BadgeDisplay::None],

        // ── Temporada / especial ──────────────────────────────────
        [BadgeCode::SeasonChampion, 'Campeón de temporada', 'Terminaste primero en la clasificación general de una temporada.', BadgeMode::Cumulative, [1, 2, 3], 'temporada', false, BadgeDisplay::Times],
        [BadgeCode::Tester, 'TESTER de Pedales', 'Participaste en la primera temporada del juego.', BadgeMode::Once, [1], 'especial', true, BadgeDisplay::None],
    ];
}
