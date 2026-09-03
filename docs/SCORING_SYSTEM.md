# Sistema de Puntuación

Motor configurable, inmutable y auditado para calcular puntos en predicciones ciclistas.

## Visión General

El sistema de puntuación de PseudoFantasy Cycling está diseñado con dos principios fundamentales:

1. **Ledger inmutable**: cada punto otorgado se registra como un `ScoreEvent` con contexto completo (usuario, regla, puntos, descripción). Nunca se almacenan solo totales — los totales se derivan sumando eventos.
2. **Configuración data-driven**: las puntuaciones no están hardcodeadas. Cada liga tiene su propio `ScoringSystem` compuesto por `ScoringRule` almacenas en base de datos, lo que permite infinitas variaciones sin tocar código.

## Arquitectura DDD

```
app/
├── Domain/
│   ├── Entities/
│   │   ├── ScoringSystem.php      ← Aggregate root inmutable
│   │   ├── ScoringRule.php         ← Regla individual
│   │   └── ScoreEvent.php          ← Ledger de puntuación
│   ├── ValueObjects/
│   │   ├── ScoringSystemType.php   ← Enum: Standard, Aggressive, Conservative, OneWeek, OneDay, Custom
│   │   ├── ScoringRuleType.php     ← Enum: 30+ tipos de regla
│   │   └── ScoringRuleContext.php   ← Enum: PreRace, PreStage
│   └── Services/
│       └── ScoringEngine.php       ← Motor de cálculo puro (domain service)
├── Application/
│   └── Services/
│       └── PreRaceScoringService.php  ← Orquestación pre-carrera
├── Infrastructure/
│   └── Persistence/Models/
│       ├── ScoringSystemModel.php
│       ├── ScoringRuleModel.php
│       └── ScoreEventModel.php
└── Presentation/
    └── Console/
        ├── ScorePreRaceCommand.php
        ├── ScoreStageCommand.php
        └── RebuildScoresCommand.php
```

El `ScoringEngine` vive en la capa Domain sin dependencias de Laravel ni Eloquent. Es un servicio puro que recibe entidades de dominio y devuelve resultados.

## Modelo de Datos

### Relaciones

```
League ──scoring_system_id──▶ ScoringSystem ──rules──▶ ScoringRule[]
                                    │
                                    ▼
                              ScoreEvent[] (ledger)
```

### ScoringSystem (Aggregate Root)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | UUID | Identificador único |
| `name` | string | Nombre visible ("Estándar Tour") |
| `type` | ScoringSystemType | Enum que clasifica el sistema |
| `description` | string | Descripción para el usuario |
| `rules` | Collection\<ScoringRule\> | Reglas que componen el sistema |

Es inmutable: `addRule()` devuelve una nueva instancia con la regla añadida.

### ScoringRule

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | UUID | Identificador único |
| `scoring_system_id` | UUID | FK al sistema padre |
| `type` | ScoringRuleType | Tipo de regla (enum de 30+ valores) |
| `context` | ScoringRuleContext | Derivado automáticamente del type |
| `points` | int | Puntos que otorga esta regla |
| `difficulty` | ?int | Nivel de dificultad de etapa (1, 2, 3). null = todas |
| `position` | ?int | Posición en clasificación (1-5). null = sin variante |

Unique constraint: `(scoring_system_id, type, difficulty, position)`

### ScoreEvent (Ledger)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | UUID | Identificador único |
| `user_id` | UUID | Usuario que recibe los puntos |
| `league_id` | UUID | Liga donde se computa |
| `scoring_rule_id` | UUID | Regla que generó el evento |
| `points` | int | Puntos otorgados (solo se persisten > 0) |
| `description` | string | Texto legible: "Acierto: Ganador de etapa (Ganador)" |
| `context` | string | Categoría para agrupación |
| `stage_id` | ?UUID | null = pre-race; no null = etapa específica |

**Nunca se modifican**: si hay un cambio, se borran los eventos de esa etapa/liga y se recalculan.

## Sistemas de Puntuación Preset

### Standard (Estándar Tour)

El sistema por defecto para Grandes Vueltas. Equilibrado entre aciertos exactos y parciales.

**Puntuación por etapa (según dificultad):**

| Regla | 1★ | 2★ | 3★ |
|-------|-----|-----|-----|
| Ganador | 10 | 18 | 25 |
| 2º clasificado | 6 | 12 | 18 |
| 3º clasificado | 4 | 8 | 12 |
| Parcial top 3 | 5 | 8 | 10 |
| Combativo | 3 | 5 | 8 |
| Líder GC | 8 | 8 | 8 |

**Pre-carrera:**

| Categoría | 1º | 2º | 3º | 4º | 5º | Parcial |
|-----------|-----|-----|-----|-----|-----|---------|
| General Top 5 | 100 | 75 | 55 | 35 | 25 | 15 |
| Maillot Verde | 40 | 25 | 15 | — | — | 10 |
| Montaña | 40 | 25 | 15 | — | — | 10 |
| Blanco | 40 | 25 | 15 | — | — | 10 |
| Equipos | 25 | — | — | — | — | — |
| Supercombativo | 25 | — | — | — | — | — |

### Aggressive (Agresivo)

Premia al ganador con mucha más diferencia. Menos puntos para posiciones secundarias.

| Regla | 1★ | 2★ | 3★ |
|-------|-----|-----|-----|
| Ganador | 15 | 30 | 45 |
| Parcial top 3 | 5 | 10 | 15 |
| Combativo | 1 | 2 | 3 |
| Líder GC | 3 | 3 | 3 |

Pre-carrera: GC 1º = 150 pts (vs 100 en Standard), Maillots 1º = 60 pts.

### Conservative (Conservador)

Puntuación más repartida. Premia la consistencia sobre los aciertos exactos.

| Regla | 1★ | 2★ | 3★ |
|-------|-----|-----|-----|
| Ganador | 8 | 16 | 25 |
| Parcial top 3 | 4 | 8 | 10 |
| Combativo | 3 | 5 | 8 |
| Líder GC | 8 | 8 | 8 |

Pre-carrera: GC Top 5 = 80/65/50/35/25 (más compacto que Standard). Equipos y Supercombativo = 35 pts.

**Forzado para ligas oficiales creadas por admin.**

### OneWeek (Carrera de una semana)

Mismo estructura de etapas pero con valores reducidos. Solo clasificación general (sin maillots ni equipos).

Pre-carrera: GC Top 3 = 70/50/35. Parcial = 15.

### OneDay (Carrera de un día / Clásicas)

Modelo completamente diferente: predicciones por posición (1º a 10º clasificado).

| Posición | Exacto | Parcial |
|----------|--------|---------|
| 1º | 100 | 35 |
| 2º | 70 | 15 |
| 3º | 50 | 12 |
| 4º | 25 | 8 |
| 5º | 20 | 6 |
| 6º | 15 | 4 |
| 7º | 12 | 3 |
| 8º | 9 | 2 |
| 9º | 6 | 1 |
| 10º | 4 | 1 |

Sin dificultad de etapa, sin maillots, sin GC — solo posiciones.

## Motor de Puntuación (ScoringEngine)

Servicio puro de dominio. Recibe un `ScoringSystem` y opcionalmente un mapa `rider_id → team_id`.

### Métodos principales

| Método | Contexto | Uso |
|--------|----------|-----|
| `calculateStageScore()` | PreStage | Predicciones antes de cada etapa |
| `calculatePositionScores()` | PreStage | Formato OneDay (posiciones 1-10) |
| `calculateGcTop5Score()` | PreRace | Top 5 de la general |
| `calculateJerseyScore()` | PreRace | Maillots (verde, montaña, blanco) |
| `calculateSimpleScore()` | PreRace | Equipos, Supercombativo |
| `calculateTotalScore()` | — | Suma puntos de una colección de eventos |

### Lógica de fallback parcial

El motor no solo comprueba aciertos exactos. Si la predicción no es exacta, intenta un fallback:

1. **Categorías de posición (OneDay)**: si el ciclista está en el top 10 pero en posición incorrecta → puntos parciales
2. **Top 3 de etapa**: si predijiste el ganador pero fue 2º o 3º → puntos de `StageTop3Partial`
3. **GC Top 5**: si el ciclista está en el top 5 real pero no en la posición predicha → puntos de `GcTop5Partial`
4. **Maillots**: si el ciclista está en el podio real pero no en la posición exacta → puntos de `{Jersey}Partial`

### Búsqueda dinámica de reglas

`findRule(type, difficulty?, position?)` busca en la colección de reglas del sistema respetando:
- El tipo de regla
- La dificultad de la etapa (si la regla tiene difficulty=null, aplica a todas)
- La posición en clasificación (para GC y maillots)

Esto permite que un mismo tipo de regla tenga valores diferentes por dificultad o posición, todo configurado en BD.

## Flujos de Puntuación

### Pre-stage (antes de cada etapa)

```
Admin guarda resultados de etapa
  → StoreStageResultUseCase::execute()
    → Valida resultados (líder GC, combativo, etc.)
    → Guarda StageResults en DB
    → Marca etapa como Finished
    → Llama a scoreStage()

scoreStage():
  → Carga StageResults terminados de la etapa
  → Carga riderTeamMap de competition_participants
  → Por cada liga de la edición:
     → Carga ScoringSystem desde DB
     → Construye ScoringSystem de dominio (buildScoringSystem)
     → Crea ScoringEngine($system, $riderTeamMap)
     → Carga predicciones PreStage de esa liga+etapa
     → Elimina score_events existentes de esa liga+etapa
     → Para cada predicción, para cada resultado:
        → $engine->calculateStageScore($prediction, $result, difficulty, stageId)
        → Si points > 0 → persiste ScoreEvent
     → Loguea actividad stage_end
```

### Pre-race (antes de la carrera completa)

```
Admin guarda clasificaciones finales
  → UpdateFinalClassificationsUseCase
    → Valida que no queden etapas sin finalizar
    → Guarda FinalClassificationModels
    → Marca edición como Finished
    → Llama a PreRaceScoringService::scoreEdition()

PreRaceScoringService::scoreEdition():
  → Carga FinalClassificationModel, agrupa por categoría
  → Construye mapas: gcTop5[], pointsPodium[], mountainsPodium[], youthPodium[]
  → Extrae: teamsWinnerId, superCombativoId
  → Por cada liga de la edición:
     → Skip si ya está puntuada (a menos que force=true)
     → Carga ScoringSystem desde DB
     → Crea ScoringEngine (sin riderTeamMap)
     → Carga predicciones pre-race (stage_id=null)
     → Dispatch por categoría:
        GcTop5       → $engine->calculateGcTop5Score()
        Points/Mtn   → $engine->calculateJerseyScore()
        TeamsWinner  → $engine->calculateSimpleScore()
        SuperCombativo → $engine->calculateSimpleScore()
     → Persiste ScoreEvents con points > 0
     → Loguea actividad (competition_start, competition_end, league_winner)
```

### Rebuild completo

```
race:rebuild-scores {league_id?}
  → Borra TODOS los score_events de la liga
  → Recalcula TODAS las puntuaciones de etapa desde cero
  → Recalcula TODAS las puntuaciones pre-race
  → Usado para corregir bugs o recalcular tras cambios de reglas
```

## Escalabilidad

El sistema está diseñado para crecer sin necesidad de reescribir lógica core:

### Añadir nuevos tipos de regla

El enum `ScoringRuleType` es el punto de extensión principal. Para añadir una nueva regla:

1. Añadir caso al enum `ScoringRuleType` con su value string
2. Definir su `label()` y `context()` (PreRace o PreStage)
3. Añadir manejo en los métodos correspondientes del `ScoringEngine`
4. Crear el `PredictionCategory` asociado si es un nuevo tipo de predicción
5. Insertar las reglas en `ScoringSystemSeeder` para los presets

**No tocar**: `ScoreEvent`, `ScoringSystem`, `ScoringRule`, ni la lógica de persistencia.

### Parámetros de dimensionamiento

Las columnas `difficulty` y `position` en `ScoringRule` permiten crear variaciones dentro de un mismo sistema:

- **difficulty** (1-3): una etapa llana puede valer menos que una de montaña
- **position** (1-5): predecir el 1º de la general vale más que el 5º

Esto se resuelve con una sola query en `findRule()` — sin lógica condicional hardcodeada.

### El tipo Custom ya existe

El enum `ScoringSystemType` incluye `Custom = 'custom'` con label "Personalizado". La arquitectura data-driven lo soporta sin cambios:

- La BD almacena cualquier combinación de reglas
- El `ScoringEngine` no hardcodea puntos — los busca dinámicamente
- Solo falta la UI de admin para crear sistemas personalizados (roadmap v2/v3)

### Añadir nuevos contextos

Si en el futuro se necesita un tercer contexto (ej: `MidRace` para predicciones durante la carrera), basta con:
1. Añadir caso al enum `ScoringRuleContext`
2. Mapear los nuevos tipos de regla a ese contexto en `ScoringRuleType::context()`
3. Crear el flujo de orquestación correspondiente en Application

## Moldeabilidad

El sistema se adapta a diferentes formatos de carrera gracias a la separación entre configuración y motor:

| Formato | Sistema preset | Predicciones | Puntuación |
|---------|---------------|--------------|------------|
| Grandes Vueltas (Tour, Giro, Vuelta) | Standard / Aggressive / Conservative | Pre-race (6 categorías) + pre-stage (5 por etapa) | Por dificultad de etapa + clasificaciones finales |
| Vueltas de 1 semana (Dauphiné, Romandía) | OneWeek | Pre-race (solo GC Top 3) + pre-stage | Etapas reducidas + GC simplificado |
| Clásicas / Monumentos (Milán-San Remo, Flandes) | OneDay | Pre-stage (posiciones 1-10) | Exacto vs parcial por posición |
| Carreras futuras | Custom | Configurable | Configurable |

### Ejemplo real: Tour de Francia con Standard

1. Admin crea edición "Tour 2026" → se auto-crea liga oficial con sistema **Standard**
2. Usuarios hacen predicciones pre-race (Top 5, maillots, equipos, supercombativo)
3. Usuarios hacen predicciones pre-stage para cada etapa (ganador, 2º, 3º, líder, combativo)
4. Tras cada etapa, admin guarda resultados → scoring automático con puntos por dificultad
5. Al finalizar, admin guarda clasificaciones finales → scoring pre-race automático
6. Clasificación de la liga se calcula sumando todos los ScoreEvents

### Ejemplo real: Milán-San Remo con OneDay

1. Admin crea edición "Milán-San Remo 2026" → se auto-crea liga oficial con sistema **OneDay**
2. Usuarios predicen top 10 clasificado (posición exacta de cada ciclista)
3. Admin guarda resultados → scoring con puntos exactos/parciales por posición

## Decisiones de Diseño

### Ledger inmutable vs totales acumulados

**Decisión**: cada punto se registra como un `ScoreEvent` independiente con contexto completo.

**Ventajas**:
- Auditoría completa: se puede reconstruir cómo se calcularon los puntos de cada usuario
- Recálculo: `race:rebuild-scores` borra y recalcula sin perder historial conceptual
- Desglose: la UI puede mostrar "30 pts por acertar ganador etapa 3, 15 pts por GC Top 5"
- Consistencia: un error de cálculo se corrige regenerando eventos, no parcheando totales

### ScoringEngine como domain service puro

**Decisión**: el motor no depende de Eloquent, Laravel ni infraestructura.

**Ventajas**:
- Testeable con 15 unit tests puros (sin DB)
- Reutilizable desde cualquier capa (CLI, HTTP, jobs)
- Independiente de la representación persistida (puede trabajar con entidades de dominio directamente)

### Ausencia de RuleSet separado

**Decisión**: la relación es `ScoringSystem → ScoringRule[]` directamente, sin entidad `RuleSet` intermedia.

El `context` (PreRace/PreStage) en cada `ScoringRule` cumple el rol que un `RuleSet` habría tenido: particionar reglas por momento de la carrera. `ScoringSystem::getRulesForContext()` provides el filtrado.

Esto simplifica el modelo manteniendo la misma funcionalidad.

## Roadmap

| Fase | Estado | Descripción |
|------|--------|-------------|
| v1 | ✅ Completado | 5 presets, scoring automático pre-stage y pre-race, ledger inmutable |
| v2 | 🔮 Planificado | Admin UI para crear sistemas de puntuación personalizados (tipo Custom) |
| v3 | 🔮 Planificado | Usuarios seleccionan "Custom" al crear liga, con editor de reglas |

## Archivos Relacionados

| Capa | Archivo | Responsabilidad |
|------|---------|-----------------|
| Domain | `app/Domain/Entities/ScoringSystem.php` | Aggregate root, colección de reglas |
| Domain | `app/Domain/Entities/ScoringRule.php` | Regla individual con type + points + difficulty + position |
| Domain | `app/Domain/Entities/ScoreEvent.php` | Ledger inmutable de puntos |
| Domain | `app/Domain/Services/ScoringEngine.php` | Motor de cálculo puro |
| Domain | `app/Domain/ValueObjects/ScoringSystemType.php` | Enum de 6 tipos de sistema |
| Domain | `app/Domain/ValueObjects/ScoringRuleType.php` | Enum de 30+ tipos de regla |
| Domain | `app/Domain/ValueObjects/ScoringRuleContext.php` | Enum PreRace / PreStage |
| Application | `app/Application/Services/PreRaceScoringService.php` | Orquestación pre-carrera por edición |
| Application | `app/Application/UseCases/Admin/Stage/StoreStageResultUseCase.php` | Guarda resultados y dispara scoring |
| Application | `app/Application/UseCases/Admin/FinalClassification/UpdateFinalClassificationsUseCase.php` | Guarda clasificaciones finales y dispara scoring pre-race |
| Infrastructure | `app/Infrastructure/Persistence/Models/ScoringSystemModel.php` | Eloquent model |
| Infrastructure | `app/Infrastructure/Persistence/Models/ScoringRuleModel.php` | Eloquent model |
| Infrastructure | `app/Infrastructure/Persistence/Models/ScoreEventModel.php` | Eloquent model |
| Presentation | `app/Presentation/Console/ScorePreRaceCommand.php` | CLI: `race:score-pre-race` |
| Presentation | `app/Presentation/Console/ScoreStageCommand.php` | CLI: `race:score-stage` |
| Presentation | `app/Presentation/Console/RebuildScoresCommand.php` | CLI: `race:rebuild-scores` |
| Seeds | `database/seeders/ScoringSystemSeeder.php` | Crea los 5 presets con sus reglas |
| Tests | `tests/Unit/Domain/Services/ScoringEngineTest.php` | 15 unit tests del motor |
