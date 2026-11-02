<?php

declare(strict_types=1);

namespace App\Application\UseCases\Prediction;

use App\Application\Exceptions\ApplicationException;
use App\Application\Services\StagePredictionCategoryService;
use App\Domain\Exceptions\AiProviderException;
use App\Domain\Interfaces\AiPredictionProviderInterface;
use App\Domain\ValueObjects\StageStatus;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Infrastructure\Persistence\Models\StageParticipantModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Genera una propuesta de pronóstico de etapa con IA.
 *
 * La propuesta NO se guarda: solo se devuelve al usuario para que la
 * revise y la modifique antes de guardar.
 */
class GenerateAiStagePredictionUseCase
{
    /**
     * Campos que la IA debe rellenar y categorías del formulario en las
     * que se materializan (la primera disponible según el ScoringSystem).
     */
    private const FIELD_CATEGORIES = [
        'stage_winner' => ['stage_winner', 'stage_position_1'],
        'stage_second' => ['stage_second', 'stage_position_2'],
        'stage_third' => ['stage_third', 'stage_position_3'],
        'stage_leader' => ['stage_leader'],
        'stage_combativo' => ['stage_combativo'],
    ];

    /** Categorías que pueden repetir corredor respecto al resto de posiciones. */
    private const DUPLICABLE_CATEGORIES = ['stage_leader', 'stage_combativo'];

    public function __construct(
        private readonly AiPredictionProviderInterface $aiPredictionProvider,
        private readonly StagePredictionCategoryService $stagePredictionCategoryService,
    ) {}

    /**
     * @return array<string, string> category => rider_id
     */
    public function execute(User $user, string $leagueId, string $stageId): array
    {
        $league = LeagueModel::findOrFail($leagueId);

        if (! $user->leagues()->where('leagues.id', $leagueId)->exists()) {
            abort(404);
        }

        $stage = StageModel::where('edition_id', $league->edition_id)
            ->findOrFail($stageId);

        if ($stage->status === StageStatus::Finished) {
            throw new ApplicationException('La etapa ya ha finalizado');
        }

        if ($stage->status === StageStatus::Ongoing
            || ($stage->scheduled_start && now()->greaterThanOrEqualTo($stage->scheduled_start))) {
            throw new ApplicationException('La etapa ya ha comenzado');
        }

        $targets = $this->resolveTargetCategories($league, $stage);

        if ($targets === []) {
            throw new ApplicationException('Esta etapa no tiene categorías de pronóstico disponibles');
        }

        $participants = $this->resolveParticipants($stage);

        if ($participants === []) {
            throw new ApplicationException('No hay participantes disponibles para esta etapa');
        }

        $context = $this->buildContext($league, $stage, $participants, array_keys($targets));

        try {
            $proposal = $this->aiPredictionProvider->proposeStagePrediction($context);
        } catch (AiProviderException $e) {
            throw new ApplicationException($e->getMessage(), previous: $e);
        }

        return $this->validateProposal($proposal, $targets, $participants);
    }

    /**
     * @return array<string, string> field => category
     */
    private function resolveTargetCategories(LeagueModel $league, StageModel $stage): array
    {
        $isTimeTrial = in_array($stage->type->value, ['time_trial', 'team_time_trial'], true);

        $formCategories = [];

        foreach ($this->stagePredictionCategoryService->forStage($league, $stage) as $rule) {
            if ($rule['category'] === 'stage_combativo' && $isTimeTrial) {
                continue;
            }

            $formCategories[] = $rule['category'];
        }

        $targets = [];
        $used = [];

        foreach (self::FIELD_CATEGORIES as $field => $candidates) {
            foreach ($candidates as $candidate) {
                if (in_array($candidate, $formCategories, true) && ! isset($used[$candidate])) {
                    $targets[$field] = $candidate;
                    $used[$candidate] = true;

                    break;
                }
            }
        }

        return $targets;
    }

    /**
     * @return array<int, array{ref: string, id: string, name: string, team: string|null}>
     */
    private function resolveParticipants(StageModel $stage): array
    {
        $hasStageParticipants = StageParticipantModel::where('stage_id', $stage->id)->exists();

        if ($hasStageParticipants) {
            $rows = DB::table('stage_participants')
                ->join('riders', 'stage_participants.rider_id', '=', 'riders.id')
                ->leftJoin('teams', 'stage_participants.team_id', '=', 'teams.id')
                ->where('stage_participants.stage_id', $stage->id)
                ->select('riders.id', 'riders.last_name', 'riders.first_name', 'teams.name as team_name')
                ->distinct()
                ->orderBy('riders.last_name')
                ->orderBy('riders.first_name')
                ->get();
        } else {
            $rows = DB::table('competition_participants')
                ->join('riders', 'competition_participants.rider_id', '=', 'riders.id')
                ->leftJoin('teams', 'competition_participants.team_id', '=', 'teams.id')
                ->where('competition_participants.edition_id', $stage->edition_id)
                ->select('riders.id', 'riders.last_name', 'riders.first_name', 'teams.name as team_name')
                ->distinct()
                ->orderBy('riders.last_name')
                ->orderBy('riders.first_name')
                ->get();
        }

        return $rows->values()->map(fn ($row, int $index) => [
            'ref' => 'R'.($index + 1),
            'id' => $row->id,
            'name' => trim("{$row->last_name} {$row->first_name}"),
            'team' => $row->team_name,
        ])->all();
    }

    /**
     * @param  array<int, array{ref: string, id: string, name: string, team: string|null}>  $participants
     * @param  array<int, string>  $fields
     * @return array<string, mixed>
     */
    private function buildContext(LeagueModel $league, StageModel $stage, array $participants, array $fields): array
    {
        $edition = $league->edition()->with('competition')->first();

        return [
            'competition' => $edition->competition->name,
            'edition_year' => $edition->year,
            'stage' => [
                'number' => $stage->number,
                'name' => $stage->name,
                'type' => $stage->type->label(),
                'distance_km' => $stage->distance,
                'elevation_gain_m' => $stage->elevation_gain,
                'difficulty' => $stage->difficulty,
                'origin' => $stage->origin,
                'destination' => $stage->destination,
                'date' => $stage->date->toDateString(),
            ],
            'participants' => array_map(
                fn (array $participant) => [
                    'ref' => $participant['ref'],
                    'name' => $participant['name'],
                    'team' => $participant['team'],
                ],
                $participants,
            ),
            'fields' => $fields,
        ];
    }

    /**
     * @param  array<string, string|null>  $proposal
     * @param  array<string, string>  $targets  field => category
     * @param  array<int, array{ref: string, id: string, name: string, team: string|null}>  $participants
     * @return array<string, string>
     */
    private function validateProposal(array $proposal, array $targets, array $participants): array
    {
        $participantsByRef = [];

        foreach ($participants as $participant) {
            $participantsByRef[$participant['ref']] = $participant;
        }

        $result = [];
        $usedRefs = [];

        foreach ($targets as $field => $category) {
            $ref = $proposal[$field] ?? null;

            if (! is_string($ref) || $ref === '' || ! isset($participantsByRef[$ref])) {
                throw new ApplicationException(
                    'La propuesta de IA incluye un corredor que no participa en esta etapa. Inténtalo de nuevo o haz tu pronóstico manualmente.',
                );
            }

            if (! in_array($category, self::DUPLICABLE_CATEGORIES, true)) {
                if (isset($usedRefs[$ref])) {
                    throw new ApplicationException(
                        'La propuesta de IA contiene corredores duplicados en las posiciones del podio. Inténtalo de nuevo o haz tu pronóstico manualmente.',
                    );
                }

                $usedRefs[$ref] = true;
            }

            $result[$category] = $participantsByRef[$ref]['id'];
        }

        return $result;
    }
}
