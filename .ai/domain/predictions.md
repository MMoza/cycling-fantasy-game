# Predicciones

## Responsabilidades
- Permitir a usuarios hacer predicciones sobre resultados ciclistas
- Bloquear predicciones en el momento correcto (inicio de etapa/carrera)
- Revelar predicciones a todos los usuarios tras el cierre
- Soportar diferentes tipos de carrera (Grand Tour, vuelta 1 semana, clásica)

## Reglas de negocio
- Predicciones pre-race: se bloquean al inicio de la primera etapa
- Predicciones pre-stage: se bloquean al inicio de esa etapa
- Antes del cierre: solo el usuario ve sus predicciones
- Después del cierre: todos ven las predicciones de todos
- Nunca se puede modificar una predicción bloqueada

## Tipos de predicción
- `pre_race`: antes de empezar la carrera
- `pre_stage`: antes de cada etapa

## Categorías de predicción
Ver `rankings.md` para lista completa.

## Invariantes
- Una predicción bloqueada no se puede modificar ni eliminar
- El `locked_at` se establece una vez y nunca cambia
- `prediction_value` es JSON flexible según categoría

## Casos límite
- Usuario hace predicción y la liga se cancela
- Etapa se suspende o cancela
- Resultados se corrigen después de calcular puntos

## Relaciones
- `User` tiene muchas `Prediction`
- `League` tiene muchas `Prediction`
- `Prediction` pertenece opcionalmente a `Stage` (requerido para pre_stage)

## Reutilización
- **Grand Tour (21 días)**: pre_race + pre_stage × 21
- **Vuelta 1 semana**: pre_race + pre_stage × 7
- **Clásica (1 día)**: solo pre_race (el "stage" es la carrera)
