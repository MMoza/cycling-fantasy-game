# ADR-0005: Laravel Breeze con Inertia para autenticación

## Estado
Accepted

## Contexto
El proyecto necesita autenticación de usuarios con:
- Login/Register
- Sesiones web
- Protección de rutas
- Integración con Inertia + React

## Decisión
Usar Laravel Breeze con stack Inertia (React + TypeScript) para autenticación inicial.

Sanctum se añadirá después solo si se necesita API externa para app móvil.

## Alternativas consideradas
- **Laravel Fortify**: Más flexible pero requiere más configuración manual.
- **Laravel Jetstream**: Demasiado pesado para las necesidades actuales.
- **Autenticación manual**: Reinventar la rueda innecesariamente.
- **Sanctum desde el inicio**: No necesario hasta que exista app móvil.

## Consecuencias
### Positivas
- Setup rápido y funcional
- Inertia ya integrado con React
- TypeScript incluido
- Fácil de personalizar

### Negativas
- Breeze asume ciertas convenciones que pueden requerir adaptación
- Si se necesita API después, habrá que añadir Sanctum
