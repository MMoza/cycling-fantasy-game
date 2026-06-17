# ADR-0007: Estrategia de Testing con Pest + Vitest + CI/CD

## Estado
Accepted

## Contexto
El proyecto necesita una estrategia de testing que:
- Cubra la lógica de negocio crítica (ScoringEngine, predicciones, reglas)
- Permita refactorizar con confianza
- Se ejecute automáticamente en CI/CD
- Cubra tanto backend como frontend
- Sea mantenible a largo plazo

## Decisión

### Backend: Pest
- Usar Pest como framework de testing (sintaxis más limpia que PHPUnit)
- Compatible con tests existentes de PHPUnit
- Estándar en el ecosistema Laravel moderno

### Frontend: Vitest + Testing Library
- Vitest como runner de tests (compatible con Vite)
- Testing Library para tests de componentes
- jsdom para simular el DOM

### CI/CD: GitHub Actions
- Pipeline en cada PR y push a main
- Orden: lint → typecheck → test → build
- Bloquear merge si tests fallan
- Reporte de cobertura

### Cobertura objetivo
- **Domain layer**: 90%+ (core del negocio, nunca debe romperse)
- **Application layer**: 80%+ (Use Cases)
- **Infrastructure**: 70%+ (Repositories, API clients)
- **Presentation**: 50%+ (Controllers finos, poco que testear)
- **Frontend**: Componentes críticos primero

### Estructura de tests
```
tests/
├── Unit/
│   ├── Domain/
│   │   ├── ValueObjects/
│   │   └── Services/
│   └── Application/
├── Feature/
│   ├── Http/
│   │   └── Controllers/
│   └── Console/
└── Integration/
    └── Repositories/

resources/js/
├── __tests__/
│   ├── components/
│   └── hooks/
```

## Alternativas consideradas
- **PHPUnit**: Rechazado porque Pest es más legible y moderno, y es compatible.
- **Cypress/Playwright para E2E**: Posible en el futuro, pero no prioritario ahora.
- **Solo tests backend**: Rechazado porque el frontend tiene lógica crítica (forms de predicciones).
- **Sin CI/CD**: Rechazado porque queremos automatización desde el inicio.

## Consecuencias
### Positivas
- Tests más legibles y mantenibles con Pest
- Cobertura completa de backend y frontend
- CI/CD previene regresiones
- Refactorización segura
- Documentación viva del comportamiento del sistema

### Negativas
- Tiempo adicional para escribir tests
- Necesidad de mantener tests al cambiar funcionalidad
- CI/CD consume minutos de GitHub Actions (gratuito para repos públicos)
