# AGENTS.md --- PseudoFantasy Cycling

## Visión

Sistema de porras y predicciones ciclistas basado en Grandes Vueltas con
soporte futuro para clásicas y vueltas de una semana.

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
-   Preparado para API futura y app móvil
-   Sin lógica de negocio en Controllers ni React Components

## Dominio principal

User -\> League -\> Edition -\> Competition

Una liga pertenece a una edición concreta.

Ejemplo:

-   Liga: Amigos del Tour
-   Edition: Tour 2026
-   Competition: Tour de Francia

## Roles

### User

-   Crear ligas
-   Unirse a ligas
-   Realizar pronósticos

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
-   Pronósticos híbridos
-   Resultados desde API externa

### v2

-   Giro y Vuelta
-   Portal de SuperAdmin
-   Creación de ligas desde administración

### v3

-   Clásicas
-   Vueltas de una semana
-   Histórico y estadísticas

## Modos de juego

### Híbrido (principal)

#### Antes del Tour

-   General Top 5
-   Maillot Verde
-   Maillot Blanco
-   Montaña
-   Equipos
-   Supercombativo

#### Antes de cada etapa

-   Ganador
-   2º clasificado
-   3º clasificado
-   Líder general
-   Combativo

Las apuestas se bloquean al inicio de la etapa.

## Visibilidad

Antes del cierre: - Solo el usuario ve sus apuestas.

Después del cierre: - Se revelan las apuestas de todos.

Nunca se puede modificar una apuesta revelada.

## Metajuego

-   El líder juega conservador.
-   El perseguidor asume riesgos.
-   Mostrar diferencia con líder.
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

## Score Events

Nunca guardar solo totales.

Guardar: - Usuario - Regla - Puntos - Descripción

Ejemplo: +30 Ganador etapa +5 Amarillo

Los totales son caché derivada.

## Sistema de reglas

Implementar:

-   RuleSet
-   ScoringRule
-   ScoringEngine

Nunca hardcodear puntuaciones.

## Integración externa

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

## Regla de oro

Diseñar para el ciclismo profesional, no para el Tour 2026.