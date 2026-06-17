# ADR-0002: UUIDs como identificadores en todas las entidades

## Estado
Accepted

## Contexto
El proyecto necesita identificadores que:
- No expongan información interna (número de registros, orden de creación)
- Sean seguros para una futura API pública
- Permitan generación descentralizada (capa de dominio, no base de datos)
- Faciliten la futura app móvil y sincronización offline

## Decisión
Usar UUID v4 como primary key en TODAS las entidades del sistema.

Implementación:
- Migraciones: `$table->uuid('id')->primary()`
- Foreign keys: `$table->uuid('entity_id')->constrained('entities')`
- Generación en capa de dominio con `Str::uuid()`
- Modelos Eloquent: `use HasUuids;` o generación manual
- Excepción: tablas pivot pueden usar composite key o UUID según necesidad

## Alternativas consideradas
- **Auto-incremental integer**: Rechazado por exponer información y dificultar API pública.
- **ULID**: Considerado pero UUID v4 tiene mejor soporte en Laravel y MySQL.
- **Snowflake IDs**: Demasiado complejos para el alcance actual.

## Consecuencias
### Positivas
- IDs no predecibles (seguridad)
- Generación sin round-trip a base de datos
- Compatible con API REST pública
- Facilita sincronización offline en app móvil

### Negativas
- Mayor tamaño de índice (36 chars vs 4-8 bytes)
- Ligeramente más lento en joins (mitigado con índices)
- URLs más largas (mitigado con slugs si es necesario)
