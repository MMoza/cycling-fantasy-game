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
- Puntuaciones variadas por usuario (645, 165, 140, 70 pts — no empatados)

### TestCompetitionSeeder
Seeder completo que genera datos de prueba realistas llamando al motor de puntuación:

1. Crea competición, edición, 3 etapas (TT, montaña, alta montaña)
2. Crea 4 usuarios + liga + sistema de puntuación Standard
3. Pre-race predictions: 6 categorías, riders variados por usuario
4. Stage results: top 3 por etapa con `is_gc_leader` e `is_combativo`
5. Stage predictions: cada usuario con patrones distintos:
   - User 0: acierta ganador/líder siempre → high scorer
   - User 1: acierta líder, falla ganador → medium scorer
   - Users 2-3: fallan todo → low/zero scorer
6. FinalClassifications para pre-race
7. Llama a `race:rebuild-scores` para generar ScoreEvents reales

## ScoringEngine

### Bugs corregidos

**Posición off-by-one** — `buildPositionMap` devuelve arrays 0-indexed pero las reglas en BD usan posiciones 1-indexed. `calculateGcTop5Score` y `calculateJerseyScore` ahora convierten con `$dbPosition = $position + 1`.

**getPredictedRiderAtPosition** — No leía la clave `rider_ids` del `prediction_value`. Ahora extrae `$riders['rider_ids'] ?? $riders`.

## RebuildScoresCommand

**stage_id faltante** — No persistía `stage_id` al insertar score events de etapa. Añadido `'stage_id' => $scoreEvent->stageId`.

## Ligas Oficiales y Restricciones por Plan (v1)

### Reglas de negocio

| ¿Quién? | ¿Puede crear? | ¿Oficial? | Scoring | Max jugadores |
|----------|--------------|-----------|---------|--------------|
| Admin (`is_admin=true`) | Sí | Sí (toggle) | Conservador (forzado) | Sin límite |
| Admin creando NO oficial | Sí | No | La que elija | 20 |
| Free (`plan=free`) | **No** | — | — | — |
| Premium (`plan=premium`) | Sí | No | La que elija | 20 |

### Join restrictions
- Free users: solo pueden unirse a ligas oficiales
- Cualquier usuario: puede unirse a ligas oficiales sin límite
- Ligas no oficiales: máximo 20 participantes

### Columnas BD
- `leagues.is_official` (boolean, default false) — añadido en `2026_07_03_000001_add_is_official_to_leagues_table.php`
- `max_players` se mantiene en la tabla pero ya no es configurable desde la UI

### UI
- **Create**: admin ve toggle "Oficial" / "Privada / Amigos". Si oficial → oculta selector scoring (forzado Conservador), oculta visibilidad (forzado público), siempre `is_public=true`
- **Show**: badge "Oficial" junto al nombre, settings sin max_players
- **Index**: badge "Oficial" en las cards

## Plan Futuro: Liga Oficial Automática por Competición

### Objetivo
Cada edición tiene automáticamente una liga oficial visible para todos. Las ligas de amigos quedan ocultas por ahora.

### Cambios necesarios

#### 1. Auto-crear liga oficial al crear edición (Backend)

**`app/Application/UseCases/Admin/Edition/StoreEditionUseCase.php`**
- Después de crear la `EditionModel`, crear automáticamente una `LeagueModel`:
  - `name`: `"Liga Oficial {competition->name} {year}"`
  - `edition_id`: la edición recién creada
  - `scoring_system_id`: sistema Conservative
  - `owner_id`: el admin que crea (se pasa como parámetro)
  - `is_official`: `true`, `is_public`: `true`
  - `invite_code`: generado automáticamente (8 chars)
  - `max_players`: 0 (sin límite para oficiales)
- El admin se auto-une como `role=owner` en `league_user`

**`app/Presentation/Http/Controllers/Admin/EditionController.php`**
- Pasar `$request->user()->id` al use case para setear el `owner_id`

#### 2. Mostrar botón "Unirte" / "Entrar" en competición (Frontend)

**`resources/js/Pages/Competitions/Show.tsx`**
- Recibir `isUserInOfficialLeague: boolean` desde el backend
- Si NO está unido → botón "Unirse a la liga oficial" que haga POST a `/leagues/join`
- Si YA está unido → botón "Entrar a la liga" que lleve a `leagues.show`

**`app/Application/UseCases/Competition/ShowCompetitionUseCase.php`**
- Añadir `isUserInOfficialLeague` al retorno, consultando `league_user` pivot

#### 3. Ocultar creación de ligas de amigos

**`resources/js/Pages/Leagues/Index.tsx`**
- Ocultar el botón "Crear liga" de la interfaz
- Solo mostrar las ligas en las que el usuario ya está unido

**`resources/js/Layouts/AppLayout.tsx`**
- Eliminar u ocultar el link "Crear liga" del menú

#### 4. Simplificar LeagueController

Los métodos `create()` y `store()` pueden mantenerse por si se reactivan en el futuro.

### Archivos a modificar
| Archivo | Cambio |
|---|---|
| `StoreEditionUseCase.php` | Auto-crear liga oficial |
| `EditionController.php` | Pasar user ID al use case |
| `ShowCompetitionUseCase.php` | Añadir `isUserInOfficialLeague` |
| `Competitions/Show.tsx` | Botón Unirte / Entrar |
| `Leagues/Index.tsx` | Ocultar "Crear liga" |
| `AppLayout.tsx` | Ocultar link de crear liga |

## Plan Futuro: Notificaciones Push con FCM

### Objetivo
Notificar a los usuarios de eventos relevantes: etapas, resultados, recordatorios y clasificación. Global por competiciones en las que participe.

### Stack
- **Firebase Cloud Messaging (FCM)** v1 API
- **`laravel-notification-channels/fcm`** — integración nativa con `Notification` de Laravel
- **PWA Push API** — service worker existente ampliado

### Migración necesaria
```sql
-- push_subscriptions
user_id (uuid FK -> users)
endpoint (string, unique)
p256dh (string)
auth (string)
user_agent (string, nullable)
last_used_at (timestamp, nullable)
```

### Eventos a notificar
| Evento | Trigger | Destinatarios | Contenido |
|--------|---------|---------------|-----------|
| Recordatorio antes de cerrar | `race:lock-predictions` (5 min antes) | Miembros sin predicción para esa etapa | "Etapa X cierra en 5 min — ¡haz tu pronóstico!" |
| Resultados publicados | `StoreStageResultUseCase::scoreStage()` | Todos los miembros de la liga | "Etapa X: resultados listos. Puntuaste +N pts" |
| Clasificación actualizada | Tras scoring de etapa | Todos los miembros de la liga | "Clasificación actualizada — estás en puesto #N" |
| Competición finalizada | `StoreStageResultUseCase` (all stages finished) | Todos los miembros | "Tour de Francia 2026 ha terminado. Posición final: #N" |

### Archivos a crear/modificar

#### Backend
| Archivo | Cambio |
|---|---|
| `database/migrations/xxx_create_push_subscriptions_table.php` | Nueva migración |
| `app/Infrastructure/Persistence/Models/PushSubscriptionModel.php` | Modelo Eloquent |
| `app/Application/Services/PushNotificationService.php` | Servicio central: `sendToLeague()`, `sendToUser()` |
| `config/firebase.php` | Configuración Firebase credentials |
| `app/Presentation/Http/Controllers/PushSubscriptionController.php` | Endpoint para registrar token |
| `app/Presentation/Console/LockPredictionsCommand.php` | Añadir recordatorio antes de lock |
| `app/Application/UseCases/Admin/Stage/StoreStageResultUseCase.php` | Añadir notificación tras scoring |
| `routes/web.php` | Ruta POST para push subscription |

#### Frontend
| Archivo | Cambio |
|---|---|
| `resources/js/hooks/usePushNotifications.ts` | Hook: permiso + token + registro |
| `resources/js/Layouts/AppLayout.tsx` | Llamar a `usePushNotifications()` |
| `public/firebase-messaging-sw.js` | Service worker FCM (push + notificationclick) |
| `public/sw.js` | Integrar firebase-messaging-sw |

#### Environment variables (Railway)
```
FIREBASE_CREDENTIALS={"type":"service_account","project_id":"...",...}
FIREBASE_PROJECT_ID=tu-project-id
```

### Flujo de suscripción
1. Usuario abre la app → `usePushNotifications()` pide permiso
2. Si acepta → browser genera push subscription (endpoint + keys)
3. Frontend envía subscription a `POST /push-subscriptions`
4. Backend guarda en `push_subscriptions` con `user_id`

### Flujo de envío (ejemplo: resultados)
```
Admin guarda resultados
  → StoreStageResultUseCase::scoreStage()
    → Itera leagues de la edición
      → PushNotificationService::sendToLeague($league, $notification)
        → Busca miembros de la liga
        → Para cada miembro: busca sus push_subscriptions
        → Envía FCM message v1 API
```

### Limpieza de tokens
- Comando `php artisan push:clean` (job programado)
- Eliminar suscripciones con `last_used_at > 30 días`
- Manejar errores FCM `404` (token inválido) → eliminar automáticamente

### Configuración Firebase (pasos manuales)
1. Crear proyecto en [Firebase Console](https://console.firebase.google.com)
2. Habilitar Cloud Messaging
3. Generar service account key (JSON)
4. Añadir `FIREBASE_CREDENTIALS` en Railway (el JSON completo)
5. Copiar `firebase-messaging-sw.js` a `/public/`