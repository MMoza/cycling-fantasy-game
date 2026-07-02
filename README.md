# PseudoFantasy Cycling

> **Predicciones ciclistas con DDD, Laravel 13 e Inertia.js** — un sistema completo de porras para Grandes Vueltas, preparado para escalar a clásicas y vueltas de una semana.

---

## Stack

| Capa | Tecnología |
| --- | --- |
| Backend | **Laravel 13**, PHP 8.4+, MySQL |
| Frontend | **React 19**, TypeScript, TailwindCSS, shadcn/ui |
| Puente | **Inertia.js** (SPA sin API boilerplate) |
| Auth | **Laravel Sanctum** + cookies |
| Infra | **Docker**, **Railway** (PaaS), **S3-compatible** (imágenes) |
| Testing | **Pest** (backend), **Vitest** (frontend), **PHPStan** max level |
| CI/CD | **GitHub Actions** en cada PR |

---

## Arquitectura

```
┌───────────────────────────────────────────────────┐
│                   Presentation                     │
│    Inertia Controllers · API Controllers · CLI     │
├───────────────────────────────────────────────────┤
│                   Application                      │
│       Use Cases · DTOs · Application Services      │
├───────────────────────────────────────────────────┤
│                  Infrastructure                    │
│    Eloquent Models · API Clients · Repositories    │
├───────────────────────────────────────────────────┤
│                     Domain                         │
│    Entities · Value Objects · Domain Services      │
└───────────────────────────────────────────────────┘
```

### Domain-Driven Design

El núcleo de dominio no depende de Laravel. Cada capa tiene responsabilidades claras:

- **Domain** — Entidades puras, Value Objects inmutables, reglas de negocio. Sin frameworks.
- **Application** — Casos de uso orquestando el dominio. DTOs inmutables.
- **Infrastructure** — Eloquent, clientes HTTP externos, implementaciones concretas de interfaces de dominio.
- **Presentation** — Controladores Inertia y API, comandos de consola. Sin lógica de negocio.

### Principios

- Mobile First
- Sin lógica de negocio en Controllers ni componentes React
- UUIDs en todas las entidades (nunca auto-incremental)
- Score Events: nunca guardar totales, cada evento de puntuación se persiste individualmente
- Sistema de puntuación configurable por reglas (`ScoringSystem → RuleSet → ScoringRule`)

---

## Infraestructura

```
Cliente (Browser)
     │
     ▼
Railway CDN ───► Nginx (Docker) ───► PHP-FPM
     │                                      │
     ▼                                      ▼
S3 (images)                          Railway MySQL
     │
cron: php artisan schedule:run (c/5 min)
```

- **Docker multi-etapa** con Nginx + PHP-FPM + start.sh
- **Railway** como PaaS con despliegue automático desde GitHub
- **S3 compatible** (storageapi.dev) para avatares de usuario con `temporaryUrl` + fallback chain
- **Cada 5 minutos**: `php artisan schedule:run` ejecuta `LockPredictionsCommand`, que bloquea apuestas 5 min antes de cada etapa
- **Zero-downtime**: Railway gestiona health checks y rollbacks

---

## Modelo de dominio

```
Competition ──► Edition ──► Stage
                   │
              League ──► User (N:M)
                   │
              ScoringSystem ──► RuleSet ──► ScoringRule
                   │
              Prediction
                   │
              ScoreEvent (auditoría)
```

### Entidades principales

| Entidad | Descripción |
| --- | --- |
| `Competition` | Competición raíz (Tour, Giro, Vuelta) |
| `Edition` | Edición concreta (Tour 2026, Giro 2027) |
| `Stage` | Etapa individual con fecha, perfil, resultados |
| `League` | Liga de usuarios con su propio sistema de puntuación |
| `Prediction` | Pronóstico: pre-race (6 categorías) o por etapa (5 markets) |
| `ScoreEvent` | Evento de puntuación atómico: `usuario + regla + puntos + contexto` |
| `ScoringSystem` | Sistema de puntuación elegido al crear la liga (no modificable) |

---

## Funcionalidades

### Para usuarios

- Crear y unirse a ligas
- Pronósticos híbridos: 6 categorías pre-race + 5 markets por etapa
- Apuestas visibles solo para el usuario hasta el cierre, luego se revelan
- Clasificación con dos vistas:
  - **General**: desglose por categoría con resultado real, predicción de cada usuario y puntos
  - **Etapas**: leaderboard por etapa seleccionable con chips de navegación
- Perfil con avatar (upload a S3)
- Búsqueda global de ligas, usuarios, etapas

### Para administradores

- Crear competiciones, ediciones, importar etapas
- Importar resultados reales (final classifications por categoría)
- Recalcular puntuaciones
- Acceso a todas las ligas

### Automatizado

- Bloqueo de apuestas 5 min antes del inicio de cada etapa (`LockPredictionsCommand`)
- Escalado de puntuación por etapas basado en reglas configurables (Standard / Aggressive / Conservative)

---

## Roadmap

| Versión | Contenido |
| --- | --- |
| **v1** | ✅ Tour de Francia, ligas, pronósticos híbridos, resultados desde API externa |
| **v2** | 🔄 Giro y Vuelta, portal SuperAdmin, creación de ligas desde admin |
| **v3** | ⏳ Clásicas, vueltas de una semana, histórico y estadísticas |

---

## Desarrollo local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run dev
```

El seeder crea:
- Usuario admin (`admin@cyclingfantasy.com` / `password`)
- Competición "Tour de Francia 2026" con 21 etapas, 23 equipos, 184 riders
- Liga de prueba "Amigos del Tour" con predicciones variadas y puntuaciones

### Testing

```bash
# Backend (Pest)
php artisan test

# Frontend (Vitest)
npx vitest run

# Static analysis
vendor/bin/phpstan analyse --level max
```

Actualmente: **134 tests** backend, todos pasando, TypeScript compila sin errores.

---

## Licencia

MIT
