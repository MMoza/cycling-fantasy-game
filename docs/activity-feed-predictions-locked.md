# Plan: Log de "Apuestas cerradas" en ActivityFeed

## Concepto

Cuando se bloquean las apuestas de una etapa, crear un log que muestre los **3 corredores más predichos como ganadores** de esa etapa en la liga.

### Ejemplo visual

```
🔒 Apuestas cerradas — Etapa 5
Favoritos: Pogačar (8), Vingegaard (5), Evenepoel (3)
hace 2h
```

## Cambios necesarios

### 1. Nuevo tipo de actividad

**`app/Domain/ValueObjects/ActivityLogType.php`**

Añadir valor:
```php
case PredictionsLocked = 'predictions_locked';
```

### 2. Nuevo método en ActivityLogService

**`app/Application/Services/ActivityLogService.php`**

Añadir método `logPredictionsLocked(LeagueModel $league, StageModel $stage, array $topRiders)`:
- Title: `"Apuestas cerradas — Etapa {number}: {name}"`
- Description: `"Favoritos: {rider1} ({count1}), {rider2} ({count2}), {rider3} ({count3})"`
- Data: `["stage_id" => ..., "top_riders" => [["rider_id" => ..., "name" => ..., "count" => N], ...]]`

### 3. Lógica en LockPredictionsCommand

**`app/Presentation/Console/LockPredictionsCommand.php`**

Dentro del loop de leagues (donde se crea `logStageStart`), añadir:

```php
// Calcular top 3 riders más predichos como ganadores
$predictions = PredictionModel::where('league_id', $league->id)
    ->where('stage_id', $stage->id)
    ->where('category', 'stage_winner')
    ->whereNotNull('locked_at')
    ->get();

$topRiders = $predictions
    ->map(fn ($p) => $p->prediction_value['rider_id'] ?? null)
    ->filter()
    ->countBy()
    ->sortDesc()
    ->take(3)
    ->keys(); // rider IDs

// Resolver nombres y crear log
if ($topRiders->isNotEmpty()) {
    $riderNames = RiderModel::whereIn('id', $topRiders)->pluck('first_name', 'id');
    $riderCounts = $predictions->map(fn ($p) => $p->prediction_value['rider_id'] ?? null)->filter()->countBy();
    
    $topRidersData = $topRiders->map(fn ($id) => [
        'rider_id' => $id,
        'name' => $riderNames[$id] ?? '—',
        'count' => $riderCounts[$id] ?? 0,
    ])->values()->all();
    
    $this->activityLogService->logPredictionsLocked($league, $stage, $topRidersData);
}
```

### 4. ActivityFeed: icono y color

**`resources/js/Pages/Leagues/components/ActivityFeed.tsx`**

Añadir al mapa `activityIcons`:
```tsx
predictions_locked: <Lock className="h-4 w-4" />,
```

Añadir al mapa `activityColors`:
```tsx
predictions_locked: 'bg-slate-100 text-slate-600 dark:bg-slate-900/20 dark:text-slate-400',
```

Importar `Lock` de lucide-react.

## Archivos a modificar

| Archivo | Cambio |
|---|---|
| `app/Domain/ValueObjects/ActivityLogType.php` | Añadir `PredictionsLocked` |
| `app/Application/Services/ActivityLogService.php` | Añadir `logPredictionsLocked()` |
| `app/Presentation/Console/LockPredictionsCommand.php` | Calcular top 3 + crear log por liga |
| `resources/js/Pages/Leagues/components/ActivityFeed.tsx` | Icono + color para `predictions_locked` |

## Notas

- Solo analiza categoría `stage_winner` (quién ganará la etapa)
- Si nadie ha predicho (0 predicciones), no se crea log
- El log se crea **una vez por liga**, no por usuario
- Importar `PredictionModel` y `RiderModel` en el command (verificar que no estén ya importados)
