# Rankings y Puntuación

## Responsabilidades
- Calcular puntos de cada usuario en una liga según su ScoringSystem
- Generar ScoreEvents para cada punto otorgado (nunca guardar solo totales)
- Mantener clasificación actualizada de la liga
- Soportar múltiples sistemas de puntuación (Standard, Aggressive, Conservative, Custom)

## Reglas de negocio
- Cada liga elige su ScoringSystem al crearse
- El ScoringSystem NO se puede cambiar una vez empezada la competición
- Los totales son caché derivada, recalculable desde ScoreEvents
- ScoreEvent contiene: usuario, regla, puntos, descripción, contexto

## Categorías de predicción (pre-race)
- `gc_top_5`: Top 5 clasificación general
- `points_winner`: Ganador maillot verde
- `youth_winner`: Ganador maillot blanco
- `mountains_winner`: Ganador maillot montaña
- `teams_winner`: Ganador clasificación equipos
- `super_combativo`: Supercombativo final

## Categorías de predicción (pre-stage)
- `stage_winner`: Ganador de etapa
- `stage_second`: 2º clasificado etapa
- `stage_third`: 3º clasificado etapa
- `stage_leader`: Líder GC tras la etapa
- `stage_combativo`: Combativo del día

## Invariantes
- Un ScoreEvent nunca se elimina, solo se recalcula
- Una predicción bloqueada no se puede modificar
- Los puntos siempre se calculan desde ScoreEvents, nunca se almacenan como total directo

## Casos límite
- Recalcular puntuaciones tras corrección de resultados
- Múltiples ligas con diferentes ScoringSystem para la misma Edition
- Predicciones parcialmente acertadas (ej: acertó 3 de 5 del GC Top 5)

## Relaciones
- `League` tiene muchos `ScoreEvent`
- `League` tiene un `ScoringSystem`
- `ScoringSystem` tiene muchas `ScoringRule`
- `User` tiene muchos `ScoreEvent` (a través de League)
