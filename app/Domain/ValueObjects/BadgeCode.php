<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum BadgeCode: string
{
    case StagesPlayed = 'stages_played';
    case StagesWon = 'stages_won';
    case GrandTourParticipation = 'grand_tour_participation';
    case MonumentParticipation = 'monument_participation';
    case ChampionshipParticipation = 'championship_participation';
    case ClassicParticipation = 'classic_participation';
    case TourWinner = 'tour_winner';
    case GiroWinner = 'giro_winner';
    case VueltaWinner = 'vuelta_winner';
    case MonumentWinner = 'monument_winner';
    case WorldChampion = 'world_champion';
    case Trilogy = 'trilogy';
    case MonumentCollection = 'monument_collection';
    case SeasonChampion = 'season_champion';
    case Tester = 'tester';

    public function label(): string
    {
        return match ($this) {
            self::StagesPlayed => 'Etapas disputadas',
            self::StagesWon => 'Etapas ganadas',
            self::GrandTourParticipation => 'Grandes Vueltas',
            self::MonumentParticipation => 'Monumentos',
            self::ChampionshipParticipation => 'Campeonatos',
            self::ClassicParticipation => 'Clásicas de 1 día',
            self::TourWinner => 'Ganador del Tour',
            self::GiroWinner => 'Ganador del Giro',
            self::VueltaWinner => 'Ganador de la Vuelta',
            self::MonumentWinner => 'Ganador de monumento',
            self::WorldChampion => 'Campeón del mundo',
            self::Trilogy => 'Trilogía',
            self::MonumentCollection => 'Colección de monumentos',
            self::SeasonChampion => 'Campeón de temporada',
            self::Tester => 'TESTER de Pedales',
        };
    }
}
