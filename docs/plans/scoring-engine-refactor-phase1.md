# Plan: Refactorización ScoringEngine — Fase 1

## Rama

`feat/scoring-engine-phase1-enum-collapse`

## Objetivo

Reducir `ScoringRuleType` de 44 cases a ~16 cases base, eliminando las variantes
numéricas repetitivas (StageExactPos1-10, StagePartialPos1-10). Crear
`ScoringRuleKey` (value object parametrizado) y `RuleTypeRegistry` (mapeo
PredictionCategory → ScoringRuleKey). Migrar datos en BD. Adaptar ScoringEngine
y consumers.

## Problema actual

- `ScoringRuleType` tiene 44 cases; 20 son variantes numéricas idénticas
- Cada nuevo tipo de predicción requiere tocar 6+ archivos
- `ScoringEngine` tiene 5 match blocks de 15-20 branches cada uno
- El dispatch de pre-race scoring está duplicado en 3 archivos

## Archivos a crear

| Archivo | Descripción |
|---|---|
| `app/Domain/ValueObjects/ScoringRuleKey.php` | Value object parametrizado |
| `app/Domain/ValueObjects/RuleTypeRegistry.php` | Mapeo Category → RuleKey |
| `database/migrations/2026_09_03_000001_migrate_scoring_rule_types.php` | Migrar datos en BD |
| `tests/Unit/Domain/ValueObjects/ScoringRuleKeyTest.php` | Tests del value object |
| `tests/Unit/Domain/ValueObjects/RuleTypeRegistryTest.php` | Tests del registry |

## Archivos a modificar

| Archivo | Cambio |
|---|---|
| `app/Domain/ValueObjects/ScoringRuleType.php` | Reducir de 44 a ~16 cases |
| `app/Domain/Entities/ScoringRule.php` | `$type` de enum a string |
| `app/Domain/Services/ScoringEngine.php` | Usar registry, eliminar match blocks |
| `app/Infrastructure/Persistence/Models/ScoringRuleModel.php` | Quitar cast enum en `type` |
| `app/Application/Services/PreRaceScoringService.php` | Simplificar dispatch |
| `app/Presentation/Console/RebuildScoresCommand.php` | Simplificar dispatch |
| `database/seeders/ScoringSystemSeeder.php` | Usar ScoringRuleKey factories |
| `tests/Unit/Domain/Services/ScoringEngineTest.php` | Adaptar referencias |

## Pasos de implementación

### 1. Crear `ScoringRuleKey` (value object)

```php
// app/Domain/ValueObjects/ScoringRuleKey.php
final readonly class ScoringRuleKey
{
    public function __construct(
        public string $baseType,     // 'stage_exact_pos', 'gc_top_5', etc.
        public ?int $position = null,
    ) {}

    public function value(): string
    {
        return $this->position !== null
            ? "{$this->baseType}_{$this->position}"
            : $this->baseType;
    }

    public static function stageExactPos(int $position): self { ... }
    public static function stagePartialPos(int $position): self { ... }
    public static function gcTop5Position(int $position): self { ... }
    public static function jerseyPosition(string $jersey, int $position): self { ... }

    public function label(): string { ... }
    public function context(): ScoringRuleContext { ... }
}
```

### 2. Crear `RuleTypeRegistry` (mapeo estático)

```php
// app/Domain/ValueObjects/RuleTypeRegistry.php
class RuleTypeRegistry
{
    public static function exactRuleFor(PredictionCategory $category): ScoringRuleKey { ... }
    public static function partialRuleFor(PredictionCategory $category): ?ScoringRuleKey { ... }
    public static function isPositionCategory(PredictionCategory $category): bool { ... }
    public static function isTop3Category(PredictionCategory $category): bool { ... }
}
```

### 3. Reducir `ScoringRuleType`

Eliminar `StageExactPos1..10` y `StagePartialPos1..10`. Quedan ~16 cases base.

### 4. Adaptar `ScoringRule` entity

`$type` cambia de `ScoringRuleType` a `string`.

### 5. Adaptar `ScoringRuleModel`

Quitar cast de `type` a enum.

### 6. Migración de datos

```
stage_exact_pos_1 → type='stage_exact_pos', position=1
stage_partial_pos_1 → type='stage_partial_pos', position=1
... (repetir para 1-10)
```

### 7. Refactorizar `ScoringEngine`

Reemplazar 5 match blocks por llamadas a `RuleTypeRegistry`.

### 8. Refactorizar consumers

Simplificar `PreRaceScoringService` y `RebuildScoresCommand`.

### 9. Refactorizar seeders

Usar `ScoringRuleKey` factories en vez de `ScoringRuleType::StageExactPos1`.

### 10. Actualizar tests

### 11. Verificar

```bash
php artisan test
vendor/bin/phpstan analyse
```

## Resultado esperado

| Métrica | Antes | Después |
|---|---|---|
| Cases en `ScoringRuleType` | 44 | ~16 |
| Líneas en `ScoringEngine` | 415 | ~250 |
| Match blocks gigantes | 5 | 0 |
| Archivos a tocar por nuevo tipo | 6+ | 2 |

## Backward compatibility

- Los values en BD (`scoring_rules.type`) se migran a formato parametrizado
- `ScoringRuleKey::value()` genera strings compatibles con los valores migrados
- No se rompe la API pública de `ScoringEngine` (mismos métodos, misma firma)
