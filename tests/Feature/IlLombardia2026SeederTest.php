<?php

declare(strict_types=1);

use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CompetitionParticipantModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Infrastructure\Persistence\Models\StageParticipantModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Infrastructure\Persistence\Models\TeamRosterModel;
use Database\Seeders\IlLombardia2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

const LOMBARDIA_STARTLIST_TEAMS = 25;
const LOMBARDIA_STARTLIST_RIDERS = 33;
const LOMBARDIA_STARTLIST_TEAMS_WITH_RIDERS = 12;

beforeEach(function () {
    $countries = [
        ['AE', 'Emiratos Árabes Unidos'],
        ['AU', 'Australia'],
        ['BE', 'Bélgica'],
        ['BH', 'Baréin'],
        ['CH', 'Suiza'],
        ['CO', 'Colombia'],
        ['DE', 'Alemania'],
        ['ES', 'España'],
        ['FR', 'Francia'],
        ['GB', 'Reino Unido'],
        ['HU', 'Hungría'],
        ['IE', 'Irlanda'],
        ['IT', 'Italia'],
        ['KZ', 'Kazajistán'],
        ['NL', 'Países Bajos'],
        ['NO', 'Noruega'],
        ['PT', 'Portugal'],
        ['SI', 'Eslovenia'],
        ['US', 'Estados Unidos'],
    ];

    foreach ($countries as [$code, $name]) {
        createCountry($code, $name);
    }

    $this->competition = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Il Lombardía',
        'type' => CompetitionType::Monument,
        'country_id' => 'IT',
        'pcs_slug' => 'il-lombardia',
        'active' => true,
    ]);

    $this->edition = EditionModel::create([
        'id' => Str::uuid()->toString(),
        'competition_id' => $this->competition->id,
        'year' => 2026,
        'start_date' => '2026-10-10',
        'end_date' => '2026-10-10',
        'status' => EditionStatus::Upcoming,
    ]);
});

describe('IlLombardia2026Seeder: teams', function () {

    test('creates every team of the pcs startlist', function () {
        $this->seed(IlLombardia2026Seeder::class);

        expect(TeamModel::count())->toBe(LOMBARDIA_STARTLIST_TEAMS);
        expect(TeamModel::where('name', 'Red Bull - BORA - hansgrohe')->exists())->toBeTrue();
        expect(TeamModel::where('name', 'Team Picnic PostNL')->exists())->toBeTrue();
    });

    test('creates teams without confirmed riders too', function () {
        $this->seed(IlLombardia2026Seeder::class);

        $cofidis = TeamModel::where('name', 'Cofidis')->first();

        expect($cofidis)->not->toBeNull();
        expect($cofidis->abbreviation)->toBe('COF');
        expect($cofidis->country_id)->toBe('FR');
    });

    test('keeps existing teams instead of duplicating them', function () {
        $existing = createTestTeam('Lidl - Trek', 'OLD', 'US');

        $this->seed(IlLombardia2026Seeder::class);

        expect(TeamModel::where('name', 'Lidl - Trek')->count())->toBe(1);
        expect($existing->fresh()->abbreviation)->toBe('OLD');
    });
});

describe('IlLombardia2026Seeder: riders', function () {

    test('creates the 33 riders of the startlist', function () {
        $this->seed(IlLombardia2026Seeder::class);

        expect(RiderModel::count())->toBe(LOMBARDIA_STARTLIST_RIDERS);

        $remco = RiderModel::where('first_name', 'Remco')
            ->where('last_name', 'Evenepoel')
            ->first();

        expect($remco)->not->toBeNull();
        expect($remco->country_id)->toBe('BE');

        $pidcock = RiderModel::where('first_name', 'Tom')
            ->where('last_name', 'Pidcock')
            ->first();

        expect($pidcock)->not->toBeNull();
        expect($pidcock->country_id)->toBe('GB');
    });

    test('does not duplicate riders that already exist in the database', function () {
        $remco = RiderModel::create([
            'id' => Str::uuid()->toString(),
            'first_name' => 'Remco',
            'last_name' => 'Evenepoel',
            'country_id' => 'BE',
        ]);

        $pidcock = RiderModel::create([
            'id' => Str::uuid()->toString(),
            'first_name' => 'Tom',
            'last_name' => 'Pidcock',
            'country_id' => 'GB',
        ]);

        $this->seed(IlLombardia2026Seeder::class);

        expect(RiderModel::count())->toBe(LOMBARDIA_STARTLIST_RIDERS);
        expect(RiderModel::where('first_name', 'Remco')->where('last_name', 'Evenepoel')->count())->toBe(1);
        expect(RiderModel::where('first_name', 'Tom')->where('last_name', 'Pidcock')->count())->toBe(1);
        expect($remco->fresh()->id)->toBe($remco->id);
        expect($pidcock->fresh()->id)->toBe($pidcock->id);
    });

    test('creates the team roster entries for the season', function () {
        $this->seed(IlLombardia2026Seeder::class);

        expect(TeamRosterModel::count())->toBe(LOMBARDIA_STARTLIST_RIDERS);
        expect(TeamRosterModel::where('year', 2026)->count())->toBe(LOMBARDIA_STARTLIST_RIDERS);
    });
});

describe('IlLombardia2026Seeder: participants', function () {

    test('adds every startlist rider to the 2026 edition', function () {
        $this->seed(IlLombardia2026Seeder::class);

        $participants = CompetitionParticipantModel::where('edition_id', $this->edition->id)->get();

        expect($participants)->toHaveCount(LOMBARDIA_STARTLIST_RIDERS);
        expect($participants->pluck('competition_id')->unique())->toHaveCount(1);
        expect($participants->first()->competition_id)->toBe($this->competition->id);
        expect($participants->pluck('rider_id')->unique())->toHaveCount(LOMBARDIA_STARTLIST_RIDERS);
        expect($participants->pluck('team_id')->unique())->toHaveCount(LOMBARDIA_STARTLIST_TEAMS_WITH_RIDERS);
    });

    test('uses the pre-existing rider when it is already in the database', function () {
        $pidcock = RiderModel::create([
            'id' => Str::uuid()->toString(),
            'first_name' => 'Tom',
            'last_name' => 'Pidcock',
            'country_id' => 'GB',
        ]);

        $this->seed(IlLombardia2026Seeder::class);

        $participant = CompetitionParticipantModel::where('rider_id', $pidcock->id)->first();

        expect($participant)->not->toBeNull();
        expect($participant->edition_id)->toBe($this->edition->id);
        expect($participant->team_id)->toBe(
            TeamModel::where('name', 'Pinarello Q36.5 Pro Cycling Team')->first()->id
        );
    });

    test('removes participants that are no longer in the startlist', function () {
        $staleTeam = createTestTeam('Equipo Fantasma', 'FAN');
        $staleRider = createTestRider('Ghost', 'Rider');
        createTestParticipant($this->competition->id, $this->edition->id, $staleTeam->id, $staleRider->id);

        $this->seed(IlLombardia2026Seeder::class);

        $participants = CompetitionParticipantModel::where('edition_id', $this->edition->id)->get();

        expect($participants)->toHaveCount(LOMBARDIA_STARTLIST_RIDERS);
        expect($participants->pluck('rider_id'))->not->toContain($staleRider->id);
    });

    test('does not touch participants of other editions', function () {
        $otherEdition = EditionModel::create([
            'id' => Str::uuid()->toString(),
            'competition_id' => $this->competition->id,
            'year' => 2025,
            'start_date' => '2025-10-11',
            'end_date' => '2025-10-11',
            'status' => EditionStatus::Finished,
        ]);

        $otherTeam = createTestTeam('Equipo Histórico', 'HIS');
        $otherRider = createTestRider('Historic', 'Rider');
        $otherParticipant = createTestParticipant(
            $this->competition->id,
            $otherEdition->id,
            $otherTeam->id,
            $otherRider->id
        );

        $this->seed(IlLombardia2026Seeder::class);

        expect(CompetitionParticipantModel::count())->toBe(LOMBARDIA_STARTLIST_RIDERS + 1);
        expect(CompetitionParticipantModel::find($otherParticipant->id))->not->toBeNull();
    });
});

describe('IlLombardia2026Seeder: stage', function () {

    test('creates the single stage of the race', function () {
        $this->seed(IlLombardia2026Seeder::class);

        $stage = StageModel::where('edition_id', $this->edition->id)->first();

        expect(StageModel::count())->toBe(1);
        expect($stage->number)->toBe(1);
        expect($stage->name)->toBe('Bergamo - Como');
        expect($stage->date->format('Y-m-d'))->toBe('2026-10-10');
        expect($stage->scheduled_start->format('Y-m-d H:i:s'))->toBe('2026-10-10 10:00:00');
        expect($stage->type)->toBe(StageType::Hill);
        expect($stage->status)->toBe(StageStatus::Upcoming);
        expect($stage->origin)->toBe('Bergamo');
        expect($stage->destination)->toBe('Como');
        expect($stage->distance)->toBe(239.0);
        expect($stage->difficulty)->toBe(3);
        expect($stage->elevation_gain)->toBe(4000);
    });

    test('adds every startlist rider to the stage', function () {
        $this->seed(IlLombardia2026Seeder::class);

        $stage = StageModel::where('edition_id', $this->edition->id)->first();
        $stageParticipants = StageParticipantModel::where('stage_id', $stage->id)->get();

        expect($stageParticipants)->toHaveCount(LOMBARDIA_STARTLIST_RIDERS);
        expect($stageParticipants->pluck('team_id')->unique())->toHaveCount(LOMBARDIA_STARTLIST_TEAMS_WITH_RIDERS);

        $pidcock = RiderModel::where('first_name', 'Tom')->where('last_name', 'Pidcock')->first();
        $pidcockParticipant = StageParticipantModel::where('stage_id', $stage->id)
            ->where('rider_id', $pidcock->id)
            ->first();

        expect($pidcockParticipant)->not->toBeNull();
        expect($pidcockParticipant->team_id)->toBe(
            TeamModel::where('name', 'Pinarello Q36.5 Pro Cycling Team')->first()->id
        );
    });

    test('keeps an existing stage of the edition', function () {
        $existingStage = StageModel::create([
            'id' => Str::uuid()->toString(),
            'edition_id' => $this->edition->id,
            'number' => 1,
            'name' => 'Etapa manual',
            'date' => '2026-10-10',
            'scheduled_start' => '2026-10-10 12:00:00',
            'type' => StageType::Hill,
            'distance' => 200.0,
            'origin' => 'Bergamo',
            'destination' => 'Como',
            'status' => StageStatus::Upcoming,
        ]);

        $this->seed(IlLombardia2026Seeder::class);

        expect(StageModel::count())->toBe(1);
        expect($existingStage->fresh()->name)->toBe('Etapa manual');
    });
});

describe('IlLombardia2026Seeder: idempotency', function () {

    test('can be run twice without duplicating anything', function () {
        $this->seed(IlLombardia2026Seeder::class);
        $this->seed(IlLombardia2026Seeder::class);

        expect(TeamModel::count())->toBe(LOMBARDIA_STARTLIST_TEAMS);
        expect(RiderModel::count())->toBe(LOMBARDIA_STARTLIST_RIDERS);
        expect(TeamRosterModel::count())->toBe(LOMBARDIA_STARTLIST_RIDERS);
        expect(CompetitionParticipantModel::count())->toBe(LOMBARDIA_STARTLIST_RIDERS);
        expect(StageModel::count())->toBe(1);
        expect(StageParticipantModel::count())->toBe(LOMBARDIA_STARTLIST_RIDERS);
    });
});

describe('IlLombardia2026Seeder: competition resolution', function () {

    test('finds the competition by the legacy name', function () {
        $this->competition->update(['name' => 'Giro de Lombardía']);

        $this->seed(IlLombardia2026Seeder::class);

        expect(CompetitionParticipantModel::count())->toBe(LOMBARDIA_STARTLIST_RIDERS);
    });

    test('finds the competition by name without accent', function () {
        $this->competition->update(['name' => 'Il Lombardia']);

        $this->seed(IlLombardia2026Seeder::class);

        expect(CompetitionParticipantModel::count())->toBe(LOMBARDIA_STARTLIST_RIDERS);
    });

    test('finds the competition by pcs slug when the name does not match', function () {
        $this->competition->update(['name' => 'Giro di Lombardia', 'pcs_slug' => 'il-lombardia']);

        $this->seed(IlLombardia2026Seeder::class);

        expect(CompetitionParticipantModel::count())->toBe(LOMBARDIA_STARTLIST_RIDERS);
    });

    test('does nothing when the competition is not found', function () {
        $this->competition->update(['name' => 'Otra competición', 'pcs_slug' => null]);

        $this->seed(IlLombardia2026Seeder::class);

        expect(TeamModel::count())->toBe(0);
        expect(RiderModel::count())->toBe(0);
        expect(CompetitionParticipantModel::count())->toBe(0);
        expect(StageModel::count())->toBe(0);
    });

    test('does nothing when the 2026 edition does not exist', function () {
        $this->edition->delete();

        $this->seed(IlLombardia2026Seeder::class);

        expect(TeamModel::count())->toBe(0);
        expect(RiderModel::count())->toBe(0);
        expect(CompetitionParticipantModel::count())->toBe(0);
        expect(StageModel::count())->toBe(0);
    });
});
