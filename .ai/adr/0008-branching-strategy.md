# ADR-0008: GitHub Flow como branching strategy

## Estado
Accepted

## Contexto
El proyecto necesita una estrategia de ramas que:
- Sea simple para un desarrollador único
- Permita desarrollo continuo sin bloqueos
- Mantenga `main` siempre en estado desplegable
- Sea fácil de aprender y mantener a largo plazo

## Decisión
Adoptar **GitHub Flow** como branching strategy:

- **`main`**: Rama principal, siempre desplegable, protegida
- **`feature/*`**: Ramas para cada funcionalidad (ej: `feature/add-competitions`)
- **Pull Requests**: Todo cambio pasa por PR antes de merge
- **CI requerido**: No se puede merge si los tests fallan
- **Tags de versión**: Solo en releases, no en cada merge

### Versionado semántico (SemVer)
Formato: `MAJOR.MINOR.PATCH`

- **MAJOR**: Cambios incompatibles (2.0.0)
- **MINOR**: Nuevas features compatibles (1.1.0)
- **PATCH**: Bug fixes (1.0.1)

Los tags se crean manualmente cuando se decide lanzar una versión, no en cada merge.

### Releases
- `v0.x.x`: Pre-producción, en desarrollo activo
- `v1.0.0`: Primera versión funcional en producción (Tour de Francia completo)

## Alternativas consideradas
- **GitFlow**: Demasiado complejo para un proyecto personal. Ramas `develop`, `release/*`, `hotfix/*` añaden overhead innecesario.
- **Trunk-based**: Requiere feature flags y deploy continuo. Demasiado arriesgado sin equipo de QA.

## Consecuencias
### Positivas
- Simple de entender y usar
- `main` siempre funcional
- CI automatiza la validación
- Fácil de explicar a futuros colaboradores

### Negativas
- No hay rama de desarrollo separada (todo va a `main`)
- Si un PR tiene un bug, hay que revertirlo
- Requiere disciplina para no mergear código roto
