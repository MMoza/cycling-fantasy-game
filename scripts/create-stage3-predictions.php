<?php

/**
 * Ejecutar con: php artisan tinker < scripts/create-stage3-predictions.php
 *
 * O copiar el contenido línea por línea en tinker.
 */

use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use Illuminate\Support\Str;

// === CONFIGURACIÓN ===
$leagueId = 'db3baa1b-7028-49df-8479-eb6d3a64e132';
$userId   = '019f242b-6742-7246-9ca1-06c752eb1a30';
$stageNumber = 3;

// === BUSCAR ETAPA ===
$league = LeagueModel::findOrFail($leagueId);
$stage = $league->stages()->where('number', $stageNumber)->firstOrFail();
echo "Etapa {$stage->number}: {$stage->name} ({$stage->id})\n";

// === BUSCAR CORREDORES (por apellido parcial) ===
$healy    = RiderModel::where('last_name', 'Healy')->firstOrFail();
$aranburu = RiderModel::where('last_name', 'Aranburu')->firstOrFail();
$pogacar  = RiderModel::where('last_name', 'Pogačar')->orWhere('last_name', 'Pogacar')->firstOrFail();
$molenaar = RiderModel::where('last_name', 'Molenaar')->firstOrFail();

echo "Healy: {$healy->full_name} ({$healy->id})\n";
echo "Aranburu: {$aranburu->full_name} ({$aranburu->id})\n";
echo "Pogacar: {$pogacar->full_name} ({$pogacar->id})\n";
echo "Molenaar: {$molenaar->full_name} ({$molenaar->id})\n";

// === BORRAR PRONÓSTICOS ANTERIORES DE ESA ETAPA (si existieran) ===
PredictionModel::where('league_id', $leagueId)
    ->where('user_id', $userId)
    ->where('stage_id', $stage->id)
    ->delete();
echo "Pronósticos anteriores eliminados.\n";

// === CREAR PRONÓSTICOS ===
$predictions = [
    ['category' => 'stage_winner',  'rider_id' => $healy->id],
    ['category' => 'stage_second', 'rider_id' => $aranburu->id],
    ['category' => 'stage_third',  'rider_id' => $pogacar->id],
    ['category' => 'stage_leader', 'rider_id' => $pogacar->id],
    ['category' => 'stage_combativo', 'rider_id' => $molenaar->id],
];

foreach ($predictions as $pred) {
    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $userId,
        'league_id' => $leagueId,
        'type' => 'pre_stage',
        'category' => $pred['category'],
        'stage_id' => $stage->id,
        'prediction_value' => ['rider_id' => $pred['rider_id']],
    ]);
    echo "Creado: {$pred['category']}\n";
}

echo "\n¡Listo! 5 pronósticos creados para la etapa {$stageNumber}.\n";
