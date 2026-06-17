# ADR-0004: Prediction System basado en categorías, no en tipo de carrera

## Estado
Accepted

## Contexto
El sistema de predicciones debe funcionar con:
- Grandes Vueltas (21 días): predicciones pre-race + pre-stage
- Vueltas de 1 semana: predicciones pre-race + pre-stage
- Clásicas (1 día): solo predicciones pre-race

El diseño inicial consideraba entidades separadas para cada tipo de predicción,
pero esto crearía duplicación de lógica y dificultaría la evolución.

## Decisión
Diseñar el Prediction System basado en **categorías de predicción**, no en tipo de carrera.

Componentes:
- `PredictionCategory` (VO): QUÉ se predice (gc_top_5, stage_winner, points_winner, etc.)
- `PredictionType` (VO): CUÁNDO se predice (pre_race, pre_stage)
- `PredictionValue` (JSON): flexible según categoría
- `stage_id`: nullable para pre_race, requerido para pre_stage

El sistema no necesita saber el tipo de carrera, solo qué stages existen y qué categorías están disponibles.

## Alternativas consideradas
- **Entidades separadas por tipo**: PreRacePrediction, StagePrediction. Rechazado por duplicación.
- **Polimorfismo con tablas separadas**: Demasiado complejo para el beneficio obtenido.
- **Configuración por tipo de carrera**: Acoplaría la lógica al tipo de carrera.

## Consecuencias
### Positivas
- Un solo modelo para todos los tipos de carrera
- Fácil añadir nuevas categorías de predicción
- Clásicas funcionan "gratis" con el mismo sistema
- Menos código, menos bugs

### Negativas
- JSON para prediction_value requiere validación cuidadosa
- Queries más complejas para filtrar por categoría
