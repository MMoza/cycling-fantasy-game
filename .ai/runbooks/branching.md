# Branching Strategy --- GitHub Flow

## Resumen
- **`main`**: Siempre desplegable, protegida
- **`feature/*`**: Una rama por funcionalidad
- **PR → CI → Merge**: Todo cambio pasa por pull request

## Flujo de trabajo

### 1. Crear una rama para una feature
```bash
git checkout main
git pull
git checkout -b feature/nombre-de-la-feature
```

### 2. Trabajar y commitear
```bash
# Código
git add -A && git commit -m "feat: descripción del cambio"

# Documentación (siempre separado)
git add -A && git commit -m "docs: actualizar documentación"
```

### 3. Push y crear PR
```bash
git push -u origin feature/nombre-de-la-feature
```
Crear PR en GitHub hacia `main`.

### 4. CI debe pasar
El workflow de GitHub Actions ejecuta:
- Lint (Pint)
- Typecheck (TypeScript)
- Test Backend (Pest)
- Test Frontend (Vitest)
- Build (Vite)

Si algo falla, arreglar y pushear de nuevo.

### 5. Merge a `main`
Una vez el CI pasa, merge el PR a `main`.

### 6. Limpiar rama
```bash
git checkout main
git pull
git branch -d feature/nombre-de-la-feature
```

## Versionado

### Cuándo crear un tag
| Situación | Tag | Ejemplo |
|---|---|---|
| Bug fix | PATCH | `v0.1.0` → `v0.1.1` |
| Nueva feature | MINOR | `v0.1.0` → `v0.2.0` |
| Cambio incompatible | MAJOR | `v0.9.0` → `v1.0.0` |

### Cómo crear un tag
```bash
# Crear tag anotado
git tag -a v0.1.0 -m "Primera versión con competiciones y ediciones"

# Push del tag
git push origin v0.1.0
```

### Cuándo crear tags en este proyecto
- **v0.1.0**: Fase 2 completada (dominio core)
- **v0.2.0**: Predicciones funcionando
- **v0.3.0**: Scoring engine funcionando
- **v1.0.0**: App en producción con Tour de Francia completo

## Branch protection (configurar en GitHub)

Ir a Settings → Branches → Add rule para `main`:
- [x] Require a pull request before merging
- [x] Require status checks to pass before merging
  - [x] Lint
  - [x] Type Check
  - [x] Test Backend
  - [x] Test Frontend
  - [x] Build
- [x] Require branches to be up to date before merging

## Convenciones de nombres

| Tipo | Patrón | Ejemplo |
|---|---|---|
| Feature | `feature/descripcion` | `feature/add-competitions` |
| Fix | `fix/descripcion` | `fix/vitest-ci-config` |
| Refactor | `refactor/descripcion` | `refactor/ddd-structure` |
| Docs | `docs/descripcion` | `docs/branching-strategy` |
| Hotfix | `hotfix/descripcion` | `hotfix/critical-bug` |

## Commits

### Estructura
```
tipo: descripción corta
```

### Tipos
| Tipo | Cuándo usar |
|---|---|
| `feat` | Nueva funcionalidad |
| `fix` | Corrección de bug |
| `refactor` | Cambio de código sin cambiar comportamiento |
| `docs` | Cambios de documentación |
| `test` | Añadir o modificar tests |
| `ci` | Cambios en CI/CD |
| `chore` | Mantenimiento, dependencias |

### Regla de oro
**Siempre 2 commits por cambio:**
1. Commit del código
2. Commit de la documentación
