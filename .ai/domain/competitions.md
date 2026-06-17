# Competiciones y Ediciones

## Responsabilidades
- **Competition**: Definir un evento ciclista (Tour de Francia, Giro, Vuelta, Clásica)
- **Edition**: Instancia concreta de una competición en un año

## Reglas de negocio
- Una Competition puede tener múltiples Edition (2025, 2026, 2027...)
- Una Edition tiene un estado: upcoming, ongoing, finished
- Las Ligas se vinculan a una Edition concreta
- Las Etapas pertenecen a una Edition

## Tipos de competición
- `grand_tour`: 21 días (Tour, Giro, Vuelta)
- `week_tour`: 7 días (París-Niza, Tirreno, etc.)
- `classic`: 1 día (Milán-San Remo, Tour de Flandes, etc.)

## Invariantes
- Una Edition no puede cambiar de Competition
- El año de una Edition es único por Competition
- Una Edition finished no acepta nuevas ligas

## Casos límite
- Edición cancelada (ej: Tour 2020 por COVID)
- Etapas reprogramadas
- Resultados corregidos post-competición

## Relaciones
- `Competition` tiene muchas `Edition`
- `Edition` pertenece a `Competition`
- `Edition` tiene muchas `League`
- `Edition` tiene muchas `Stage`
