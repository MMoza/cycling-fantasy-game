# Ligas

## Responsabilidades
- Agrupar usuarios para competir entre sí
- Elegir qué Edition del ciclismo seguir
- Configurar sistema de puntuación propio (ScoringSystem)
- Gestionar membresía (owner, members)
- Generar código de invitación

## Reglas de negocio
- Una liga pertenece a una Edition concreta
- Una liga tiene un ScoringSystem elegido al crearla
- El ScoringSystem NO se puede cambiar una vez empezada la competición
- Solo el owner puede eliminar la liga
- Los miembros pueden unirse con código de invitación

## Invariantes
- Una liga siempre tiene al menos un miembro (el owner)
- El owner no puede abandonar la liga sin transferir ownership o eliminarla
- El código de invitación es único por liga

## Casos límite
- Owner abandona la liga (transferir ownership)
- Liga sin actividad (¿eliminar automáticamente?)
- Usuario intenta unirse a liga de Edition ya finalizada

## Relaciones
- `League` pertenece a `Edition`
- `League` tiene un `ScoringSystem`
- `League` tiene muchos `User` (via pivot)
- `League` tiene muchas `Prediction`
- `League` tiene muchos `ScoreEvent`
