# ADR-0003: ScoringSystem vinculado a League

## Estado
Accepted

## Contexto
El sistema de puntuación debe ser configurable por cada liga/competición de amigos.
Surge la duda de dónde debe vivir la configuración del sistema de puntuación:
- ¿En Competition (Tour de Francia)?
- ¿En Edition (Tour 2026)?
- ¿En League (Amigos del Tour)?

## Decisión
El `ScoringSystem` se vincula a `League`, no a `Competition` ni `Edition`.

Cada grupo de amigos puede elegir cómo puntuar aunque sigan la misma edición del Tour.
El sistema NO se puede cambiar una vez empezada la competición.

Relación: `League -> ScoringSystem -> RuleSet -> ScoringRule[]`

## Alternativas consideradas
- **ScoringSystem en Competition**: Todos puntúan igual en esa competición. Rechazado porque elimina la personalización por liga.
- **ScoringSystem en Edition**: Todos puntúan igual en esa edición. Rechazado por la misma razón.
- **ScoringSystem en League con override por Competition**: Demasiado complejo para v1.

## Consecuencias
### Positivas
- Cada liga tiene su propia personalidad de puntuación
- Los amigos pueden elegir sistemas más agresivos o conservadores
- Fácil de entender y explicar al usuario

### Negativas
- Mayor complejidad en cálculos (cada liga tiene su propio sistema)
- Comparaciones entre ligas más difíciles (diferentes sistemas)
