# AGENTS.md --- PseudoFantasy Cycling

## Visi�n

Sistema de porras y predicciones ciclistas basado en Grandes Vueltas con
soporte futuro para cl�sicas y vueltas de una semana.

## Stack

### Backend

-   Laravel 13
-   PHP 8.4+
-   MySQL
-   Sanctum

### Frontend

-   React + TypeScript
-   InertiaJS
-   TailwindCSS
-   shadcn/ui

## Principios

-   Mobile First
-   DDD
-   Arquitectura desacoplada
-   Preparado para API futura y app m�vil
-   Sin l�gica de negocio en Controllers ni React Components

## Dominio principal

User -\> League -\> Edition -\> Competition

Una liga pertenece a una edici�n concreta.

Ejemplo:

-   Liga: Amigos del Tour
-   Edition: Tour 2026
-   Competition: Tour de Francia

## Roles

### User

-   Crear ligas
-   Unirse a ligas
-   Realizar pron�sticos

### SuperAdmin

-   Acceso a todas las ligas
-   Crear competiciones
-   Crear ediciones
-   Importar etapas
-   Importar resultados
-   Recalcular puntuaciones

## Roadmap

### v1

-   Solo Tour de Francia
-   Crear y unirse a ligas
-   Pron�sticos h�bridos
-   Resultados desde API externa

### v2

-   Giro y Vuelta
-   Portal de SuperAdmin
-   Creaci�n de ligas desde administraci�n

### v3

-   Cl�sicas
-   Vueltas de una semana
-   Hist�rico y estad�sticas

## Modos de juego

### H�brido (principal)

#### Antes del Tour

-   General Top 5
-   Maillot Verde
-   Maillot Blanco
-   Monta�a
-   Equipos
-   Supercombativo

#### Antes de cada etapa

-   Ganador
-   2� clasificado
-   3� clasificado
-   L�der general
-   Combativo

Las apuestas se bloquean al inicio de la etapa.

## Visibilidad

Antes del cierre: - Solo el usuario ve sus apuestas.

Despu�s del cierre: - Se revelan las apuestas de todos.

Nunca se puede modificar una apuesta revelada.

## Metajuego

-   El l�der juega conservador.
-   El perseguidor asume riesgos.
-   Mostrar diferencia con l�der.
-   Mostrar puntos restantes.

## Arquitectura DDD

app/ - Domain/ - Application/ - Infrastructure/ - Presentation/

### Domain

-   Entities
-   Value Objects
-   Domain Services
-   Interfaces

No depende de Laravel.

### Application

-   Use Cases
-   DTOs
-   Services

### Infrastructure

-   Eloquent
-   API Clients
-   Repositories

### Presentation

-   Inertia Controllers
-   API Controllers
-   Console Commands

## Entidades

-   User
-   League
-   Competition
-   Edition
-   Stage
-   Prediction
-   Result
-   ScoreEvent
-   ScoringSystem

## Score Events

Nunca guardar solo totales.

Guardar: - Usuario - Regla - Puntos - Descripci�n

Ejemplo: +30 Ganador etapa +5 Amarillo

Los totales son cach� derivada.

## Sistema de puntuación configurable

Cada competición/liga elige su propio sistema de puntuación al crearse.

### ScoringSystem

Entidad que define cómo se puntúa en una competición.

-   Contiene o referencia un `RuleSet`
-   Tiene nombre y descripción visible al usuario
-   NO se puede cambiar una vez empezada la competición

### Tipos preset (v1)

-   **Standard**: Puntuación equilibrada
-   **Aggressive**: Premia más al ganador, menos al resto
-   **Conservative**: Puntuación más repartida

### Custom (v2/v3)

-   Tipo `Custom` permite reglas propias definidas por el usuario
-   Preparar arquitectura ahora para no romper después

### Relación

```
League -> ScoringSystem -> RuleSet -> ScoringRule[]
```

El `ScoringEngine` recibe el `ScoringSystem` de la competición y calcula según sus reglas.

## Sistema de reglas

Implementar:

-   ScoringSystem
-   RuleSet
-   ScoringRule
-   ScoringEngine

Nunca hardcodear puntuaciones.

## Integraci�n externa

Usar API de terceros.

Crear: CyclingApiClientInterface

## Comandos

-   race:update-stage
-   race:update-classifications
-   race:score-stage
-   race:rebuild-scores

Todos reutilizan Application Services.

## Convenciones

-   PHPStan máximo nivel
-   Laravel Pint
-   DTOs inmutables
-   Value Objects
-   Strict types
-   **UUIDs en todas las entidades** (nunca auto-incremental IDs)
-   **Testing con Pest** (backend) y **Vitest** (frontend)
-   CI/CD con GitHub Actions en cada PR

## Identificadores

-   Todas las tablas usan `CHAR(36)` para primary keys (UUID v4)
-   Laravel: `$table->uuid('id')->primary()`
-   Modelos: `use HasUuids;` o generación manual con `Str::uuid()`
-   Foreign keys: `$table->uuid('user_id')->constrained('users')`
-   Los UUIDs se generan en la capa de dominio, no en la base de datos
-   Excepción: tablas pivot pueden usar composite key o UUID según necesidad

## Regla de oro

Dise�ar para el ciclismo profesional, no para el Tour 2026.

## Seeder: Rider Data (TourDeFrance2026Seeder)

### Source
https://www.procyclingstats.com/race/tour-de-france/2026/startlist

### Stats
- 184 riders (23 teams x 8) — exact PCS startlist
- Fields: `first_name`, `last_name`, `country_id` (ISO 3166-1 alpha-2)

### Removed riders (not in 2026 startlist)
Mikel Landa, Carlos Rodríguez, David Gaudu, Giulio Ciccone, Kaden Groves, Stefan Bissegger, Roger Adrià, Lorenzo Fortunato, Emanuel Buchmann, Søren Kragh Andersen

### Naming notes
- Compound surnames: "Braz Afonso", "García Pierna", "Van den Broek", "Van der Poel", "Del Toro", "Le Berre", "De Kleijn", "De Lie", "Van Eetvelt", "Van Gils", "Van Dijke", "Van Baarle", "Van Wilder", "Van Lerberghe", "Van Mechelen", "Van Asbroeck", "Van Moer", "Van den Berg"
- Compound first names: "Tobias Halland Johannessen", "Anders Halland Johannessen", "Per Strand Hagenes", "José Félix Parra", "Xabier Mikel Azparren"
- Special chars: "Pogačar", "Mohorič", "Grégoire", "Michał", "Skujiņš", "Wærenskjold", "Træen", "Märkl", "Großschartner", "Aurélien", "Clément", "Mattéo", "Thibault", "Kévin", "Márkl"

### To update startlist
Replace the `$ridersByTeam` array in `createRosters()` keeping format: `['first' => '...', 'last' => '...', 'country' => 'XX']`.

## RiderSeeder

Seeder independiente que solo añade riders a la tabla `riders`. Sin equipos, rosters, participantes ni etapas.

### Uso (seguro en prod)
```bash
php artisan db:seed --class=RiderSeeder --force
```

### Comportamiento
- `firstOrCreate` por `first_name` + `last_name`
- Cero side effects: no borra ni modifica nada existente
- Ignora riders que ya están en la DB
- Añade los 184 riders del TDF 2026 como pool base

## Clasificación

### Página de clasificación (`/leagues/{id}/classification`)

Dos pestañas:

#### General
- Desglose por categoría (Top 5 General, Maillot Verde, Montaña, Blanco, Equipos, Supercombativo)
- Cada categoría muestra el resultado real + lo que pronosticó cada usuario + puntos obtenidos
- Fuente: `FinalClassificationModel` (resultados reales), `PredictionModel` (predicciones), `ScoreEventModel` (puntos agrupados por `user_id + context`)

#### Etapas
- Chips de etapa (solo etapas con puntuaciones)
- Por defecto selecciona la última etapa puntuada
- Leaderboard por etapa basado en `ScoreEventModel` agrupado por `(user_id, stage_id)`

### Backend: `ShowClassificationUseCase`

Retorna:
- `general_leaderboard`: suma de puntos sin `stage_id` (predicciones pre-race)
- `stage_leaderboards[]`: leaderboard por etapa
- `general_details[]`: array con `{category, label, actual[], users[]}` donde cada user tiene `{user_name, is_current_user, predicted, points}`
- `stages[]`: lista de etapas con `has_scores`
- `last_scored_stage_id`: última etapa con puntuaciones
- `user_position`: rank/points/behind_leader del usuario actual

### Test data
- Liga de prueba con 5 usuarios, 3 etapas finalizadas, predicciones variadas
- ScoreEvents: generales (context=category) y por etapa (stage_id not null)
- Puntuaciones variadas (0-35 puntos por usuario, no todos empatados)