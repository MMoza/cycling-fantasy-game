# ADR-0001: Arquitectura Domain-Driven Design (DDD)

## Estado
Accepted

## Contexto
El proyecto PseudoFantasy Cycling requiere una arquitectura que permita:
- Evolución a largo plazo (v1: Tour, v2: Giro/Vuelta, v3: Clásicas)
- Reglas de negocio complejas (sistemas de puntuación configurables)
- Preparación para API futura y app móvil
- Mantenibilidad con múltiples desarrolladores o agentes IA

## Decisión
Adoptar arquitectura DDD con 4 capas:
- **Domain**: Entities, Value Objects, Domain Services, Interfaces (sin dependencias externas)
- **Application**: Use Cases, DTOs, Application Services
- **Infrastructure**: Eloquent Models, Repositories, API Clients
- **Presentation**: Controllers, Console Commands, Inertia pages

Las dependencias fluyen: Presentation → Application → Domain ← Infrastructure

## Alternativas consideradas
- **Arquitectura MVC tradicional**: Rechazada por acoplar lógica de negocio a controllers y dificultar la evolución.
- **Clean Architecture**: Similar pero más compleja; DDD es suficiente para el alcance del proyecto.
- **Modular monolith**: Posible evolución futura si el dominio crece significativamente.

## Consecuencias
### Positivas
- Lógica de negocio aislada y testeable
- Fácil sustitución de infraestructura (ej: cambiar API de ciclismo)
- Preparado para extraer servicios en el futuro
- Documentación clara del dominio

### Negativas
- Mayor boilerplate inicial
- Curva de aprendizaje para desarrolladores no familiarizados con DDD
- Más archivos por feature
