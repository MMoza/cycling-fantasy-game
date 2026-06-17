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

-   PHPStan m�ximo nivel
-   Laravel Pint
-   DTOs inmutables
-   Value Objects
-   Strict types

## Regla de oro

Dise�ar para el ciclismo profesional, no para el Tour 2026.