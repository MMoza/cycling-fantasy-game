<?php

declare(strict_types=1);

use App\Domain\Exceptions\AiProviderException;
use App\Domain\Interfaces\AiPredictionProviderInterface;
use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\ScoringRuleContext;
use App\Domain\ValueObjects\ScoringRuleType;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\ScoringRuleModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->fake = new class implements AiPredictionProviderInterface
    {
        /** @var array<int, array<string, mixed>> */
        public array $calls = [];

        /** @var array<string, string|null>|null */
        public ?array $response = null;

        public ?AiProviderException $exception = null;

        public function proposeStagePrediction(array $context): array
        {
            $this->calls[] = $context;

            if ($this->exception !== null) {
                throw $this->exception;
            }

            return $this->response ?? [];
        }
    };

    $this->app->instance(AiPredictionProviderInterface::class, $this->fake);

    $this->user = User::factory()->create();

    $competition = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Tour de Francia',
        'type' => CompetitionType::GC,
        'country_id' => createCountry(),
        'active' => true,
    ]);

    $this->edition = EditionModel::create([
        'id' => Str::uuid()->toString(),
        'competition_id' => $competition->id,
        'year' => 2026,
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-23',
        'status' => EditionStatus::Upcoming,
    ]);

    $this->scoringSystem = ScoringSystemModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Estándar',
        'type' => ScoringSystemType::Standard,
        'description' => 'Puntuación equilibrada',
    ]);

    $this->league = LeagueModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Amigos del Tour',
        'edition_id' => $this->edition->id,
        'scoring_system_id' => $this->scoringSystem->id,
        'owner_id' => $this->user->id,
        'invite_code' => Str::random(8),
        'is_public' => false,
    ]);

    $this->league->users()->attach($this->user->id, [
        'id' => Str::uuid()->toString(),
        'role' => 'owner',
    ]);

    $this->stage = StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $this->edition->id,
        'number' => 5,
        'name' => 'Lille → Paris',
        'date' => '2026-07-05',
        'scheduled_start' => now()->addDay(),
        'type' => StageType::Flat,
        'distance' => 180.5,
        'origin' => 'Lille',
        'destination' => 'Paris',
        'status' => StageStatus::Upcoming,
    ]);

    $this->team = createTestTeam('Team Test', 'TST');
    $this->riders = [
        createTestRider('Rider', 'A'),
        createTestRider('Rider', 'B'),
        createTestRider('Rider', 'C'),
        createTestRider('Rider', 'D'),
        createTestRider('Rider', 'E'),
    ];

    foreach ($this->riders as $rider) {
        createTestParticipant($this->edition->competition_id, $this->edition->id, $this->team->id, $rider->id);
    }

    foreach ([
        ['type' => ScoringRuleType::StageWinner, 'points' => 10],
        ['type' => ScoringRuleType::StageSecond, 'points' => 6],
        ['type' => ScoringRuleType::StageThird, 'points' => 4],
        ['type' => ScoringRuleType::StageLeader, 'points' => 8],
        ['type' => ScoringRuleType::StageCombativo, 'points' => 3],
    ] as $rule) {
        ScoringRuleModel::create([
            'id' => Str::uuid()->toString(),
            'scoring_system_id' => $this->scoringSystem->id,
            'type' => $rule['type'],
            'context' => ScoringRuleContext::PreStage,
            'points' => $rule['points'],
            'difficulty' => null,
            'position' => null,
        ]);
    }
});

test('guest cannot generate ai proposals', function () {
    $response = $this->post(route('predictions.ai.generate', [$this->league->id, $this->stage->id]));

    $response->assertRedirect(route('login'));
    expect($this->fake->calls)->toBeEmpty();
});

test('user not in league cannot generate ai proposals', function () {
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)->postJson(route('predictions.ai.generate', [$this->league->id, $this->stage->id]));

    $response->assertNotFound();
    expect($this->fake->calls)->toBeEmpty();
});

test('returns a proposal mapped to rider ids without saving anything', function () {
    $this->fake->response = [
        'stage_winner' => 'R1',
        'stage_second' => 'R2',
        'stage_third' => 'R3',
        'stage_leader' => 'R1',
        'stage_combativo' => 'R5',
    ];

    $response = $this->actingAs($this->user)->postJson(route('predictions.ai.generate', [$this->league->id, $this->stage->id]));

    $response->assertOk()->assertJson([
        'predictions' => [
            'stage_winner' => $this->riders[0]->id,
            'stage_second' => $this->riders[1]->id,
            'stage_third' => $this->riders[2]->id,
            'stage_leader' => $this->riders[0]->id,
            'stage_combativo' => $this->riders[4]->id,
        ],
    ]);

    $this->assertDatabaseCount('predictions', 0);
});

test('sends real stage and participant data to the provider', function () {
    $this->fake->response = [
        'stage_winner' => 'R1',
        'stage_second' => 'R2',
        'stage_third' => 'R3',
        'stage_leader' => 'R1',
        'stage_combativo' => 'R5',
    ];

    $this->actingAs($this->user)->postJson(route('predictions.ai.generate', [$this->league->id, $this->stage->id]))
        ->assertOk();

    $context = $this->fake->calls[0];

    expect($context['competition'])->toBe('Tour de Francia');
    expect($context['edition_year'])->toBe(2026);
    expect($context['stage']['name'])->toBe('Lille → Paris');
    expect($context['stage']['type'])->toBe('Llano');
    expect($context['fields'])->toBe(['stage_winner', 'stage_second', 'stage_third', 'stage_leader', 'stage_combativo']);

    $participants = collect($context['participants']);
    expect($participants)->toHaveCount(5);
    expect($participants->first())->toBe(['ref' => 'R1', 'name' => 'A Rider', 'team' => 'Team Test']);
});

test('rejects proposals with riders outside the participant list', function () {
    $this->fake->response = [
        'stage_winner' => 'R99',
        'stage_second' => 'R2',
        'stage_third' => 'R3',
        'stage_leader' => 'R1',
        'stage_combativo' => 'R5',
    ];

    $response = $this->actingAs($this->user)->postJson(route('predictions.ai.generate', [$this->league->id, $this->stage->id]));

    $response->assertStatus(422);
    expect($response->json('message'))->toContain('no participa en esta etapa');
    $this->assertDatabaseCount('predictions', 0);
});

test('rejects proposals with duplicated podium riders', function () {
    $this->fake->response = [
        'stage_winner' => 'R1',
        'stage_second' => 'R1',
        'stage_third' => 'R3',
        'stage_leader' => 'R1',
        'stage_combativo' => 'R5',
    ];

    $response = $this->actingAs($this->user)->postJson(route('predictions.ai.generate', [$this->league->id, $this->stage->id]));

    $response->assertStatus(422);
    expect($response->json('message'))->toContain('duplicados');
    $this->assertDatabaseCount('predictions', 0);
});

test('rejects incomplete proposals', function () {
    $this->fake->response = [
        'stage_winner' => 'R1',
        'stage_second' => null,
        'stage_third' => 'R3',
        'stage_leader' => 'R1',
        'stage_combativo' => 'R5',
    ];

    $response = $this->actingAs($this->user)->postJson(route('predictions.ai.generate', [$this->league->id, $this->stage->id]));

    $response->assertStatus(422);
    expect($response->json('message'))->toContain('Inténtalo de nuevo');
});

test('returns a friendly error when the provider fails', function () {
    $this->fake->exception = new AiProviderException('El asistente de IA no está disponible en este momento. Haz tu pronóstico manualmente.');

    $response = $this->actingAs($this->user)->postJson(route('predictions.ai.generate', [$this->league->id, $this->stage->id]));

    $response->assertStatus(422);
    expect($response->json('message'))->toContain('El asistente de IA no está disponible');
    expect($response->json('message'))->toContain('Haz tu pronóstico manualmente');
});

test('rejects generation when the stage has started', function () {
    $this->stage->update(['scheduled_start' => now()->subHour()]);

    $response = $this->actingAs($this->user)->postJson(route('predictions.ai.generate', [$this->league->id, $this->stage->id]));

    $response->assertStatus(422);
    expect($response->json('message'))->toContain('La etapa ya ha comenzado');
    expect($this->fake->calls)->toBeEmpty();
});

test('does not ask for the combative rider in time trials', function () {
    $this->stage->update(['type' => StageType::TimeTrial]);

    $this->fake->response = [
        'stage_winner' => 'R1',
        'stage_second' => 'R2',
        'stage_third' => 'R3',
        'stage_leader' => 'R1',
    ];

    $response = $this->actingAs($this->user)->postJson(route('predictions.ai.generate', [$this->league->id, $this->stage->id]));

    $response->assertOk()->assertJsonMissingPath('predictions.stage_combativo');
    expect($this->fake->calls[0]['fields'])->toBe(['stage_winner', 'stage_second', 'stage_third', 'stage_leader']);
});

test('maps podium to exact position categories when the scoring system uses them', function () {
    ScoringRuleModel::query()->delete();

    foreach ([
        ['type' => ScoringRuleType::StageExactPos1, 'points' => 10, 'position' => 1],
        ['type' => ScoringRuleType::StageExactPos2, 'points' => 8, 'position' => 2],
        ['type' => ScoringRuleType::StageExactPos3, 'points' => 6, 'position' => 3],
        ['type' => ScoringRuleType::StageLeader, 'points' => 4, 'position' => null],
    ] as $rule) {
        ScoringRuleModel::create([
            'id' => Str::uuid()->toString(),
            'scoring_system_id' => $this->scoringSystem->id,
            'type' => $rule['type'],
            'context' => ScoringRuleContext::PreStage,
            'points' => $rule['points'],
            'difficulty' => null,
            'position' => $rule['position'],
        ]);
    }

    $this->fake->response = [
        'stage_winner' => 'R1',
        'stage_second' => 'R2',
        'stage_third' => 'R3',
        'stage_leader' => 'R1',
        'stage_combativo' => 'R5',
    ];

    $response = $this->actingAs($this->user)->postJson(route('predictions.ai.generate', [$this->league->id, $this->stage->id]));

    $response->assertOk()->assertJson([
        'predictions' => [
            'stage_position_1' => $this->riders[0]->id,
            'stage_position_2' => $this->riders[1]->id,
            'stage_position_3' => $this->riders[2]->id,
            'stage_leader' => $this->riders[0]->id,
        ],
    ]);
});
