# Plan de Observabilidad y Estado — Pedales

## Objetivo

Preparar Pedales (Laravel + React + Inertia) para su apertura al público en 2027 con una base de observabilidad y mantenimiento que permita detectar problemas, diagnosticarlos rápidamente, monitorizar la salud técnica y de negocio y ofrecer un estado público seguro.

> Principio: primero construir visibilidad sobre el sistema; después exponer una versión segura y comprensible para los usuarios.

## Contexto del proyecto

| Área | Situación actual |
|---|---|
| Error tracking | Ninguno |
| Métricas | Ninguna |
| Debugging dev | Ninguno |
| Health checks | Solo `/up` básico de Laravel |
| Logs | Monolog local en `storage/logs/` |
| Queue | Database driver |
| Cache | Database por defecto |
| Infra | Railway |
| Redis | Configurado pero sin usar |

## 1. Observabilidad interna

### Sentry
Integrar Sentry en Laravel y React para registrar:
- Excepciones y errores frontend/backend.
- Stack traces.
- Ruta/endpoint.
- Release/version.
- Contexto de usuario cuando sea apropiado.
- Frecuencia y usuarios afectados.

No registrar contraseñas, tokens ni datos personales innecesarios.

### Laravel Pulse
Usarlo como primera capa de métricas:
- Requests y rendimiento.
- Excepciones.
- Jobs.
- Queries lentas.
- Utilización general.

> Requiere Redis como backend.

### Laravel Telescope
Usarlo principalmente en desarrollo/staging y para diagnóstico controlado:
- Requests.
- Queries.
- Jobs.
- Exceptions.
- Cache/Redis.
- Notifications.
- Commands.

Debe estar protegido y nunca expuesto públicamente.

## 2. Health Checks

Crear un `StatusService`:

```text
StatusService
├── checkApplication()
├── checkDatabase()
├── checkCache()
├── checkQueue()
├── checkPredictions()
├── checkResults()
└── checkNotifications()
```

Los checks deben validar funcionalidades reales, no solo que el proceso esté levantado.

Comprobar inicialmente:
- Aplicación.
- Base de datos.
- Redis/cache.
- Queue.
- Predicciones.
- Resultados.
- Notificaciones.

## 3. Logs estructurados

Revisar progresivamente los logs para registrar eventos estructurados:

```php
Log::info('stage_processing_completed', [
    'stage_id' => $stage->id,
    'result_id' => $result->id,
]);
```

Registrar eventos importantes de etapas, resultados, predicciones, clasificaciones, jobs e integraciones.

Nunca registrar credenciales, tokens o datos personales/sensibles innecesarios.

## 4. Métricas de negocio

Monitorizar:
- Usuarios registrados y activos.
- Predicciones realizadas.
- Predicciones por etapa.
- Etapas y resultados procesados.
- Clasificaciones generadas.
- Jobs ejecutados/fallidos.
- Notificaciones.
- Errores de procesamiento.

## 5. Panel privado

Crear:

```text
/admin/observabilidad
```

Mostrar:
- Estado general.
- Aplicación, DB, Redis, queue y servicios funcionales.
- Requests y latencia.
- Errores recientes.
- Jobs fallidos.
- Métricas de negocio.
- Incidencias activas y recientes.

## 6. Sistema de incidencias

Crear una entidad `Incident` o equivalente:

```text
Incident
├── title
├── description
├── severity
├── status
├── started_at
├── resolved_at
└── affected_services
```

Estados:
- `investigating`
- `identified`
- `monitoring`
- `resolved`

Severidades:
- `minor`
- `major`
- `critical`

No convertir automáticamente cada excepción técnica en una incidencia pública.

**Campo adicional:** `auto_detected` (boolean) para diferenciar incidencias creadas manualmente vs automáticas desde health checks.

## 7. Observabilidad pública

Crear una página Inertia:

```text
/estado
```

No crear una API REST solo para esto.

Mostrar únicamente información útil para usuarios:

```text
🟢 Todos los sistemas operativos

Aplicación             Operativa
Predicciones           Operativas
Clasificaciones        Operativas
Resultados             Operativos
Notificaciones         Operativas
```

Estados:
- 🟢 Operativo.
- 🟡 Rendimiento degradado.
- 🔴 Incidencia.

No mostrar CPU, memoria, IPs, servidores, queries, stack traces, IDs internos ni datos de usuarios.

## 8. Histórico público

Mostrar incidencias recientes:

```text
Hoy
🟢 Sin incidencias

Ayer
🟢 Sin incidencias

12 septiembre
🟡 Retraso en actualización de resultados
   Resuelto · 18:42
```

Empezar con histórico de 30 días y ampliar a 90 si tiene sentido.

## 9. Actualización

La página `/estado` puede refrescarse periódicamente mediante Inertia, sin introducir una API.

Flujo:

```text
/estado
   ↓
Laravel
   ↓
StatusService
   ↓
Inertia
   ↓
React
```

Intervalo inicial: 30–60 segundos. Usar partial reloads de Inertia para minimizar carga.

## 10. Alertas

Añadir después de estabilizar los health checks:
- Aplicación caída.
- DB caída.
- Queue bloqueada.
- Aumento anormal de errores.
- Jobs fallidos.
- Procesamiento de resultados detenido.
- Degradación significativa.

Evitar alertas ruidosas.

## 11. Uptime externo

Añadir posteriormente monitorización externa para comprobar Pedales desde fuera.

Inicialmente:
- Web principal.
- Health endpoint.

No depender exclusivamente de la monitorización interna.

# Orden de implementación

## Sprint 1 — Base

Prerequisito: migrar cache y queue a Redis (ya configurado en infra pero sin usar).

- [ ] Migrar cache a Redis (`CACHE_STORE=redis`).
- [ ] Migrar queue a Redis (`QUEUE_CONNECTION=redis`).
- [ ] Sentry Laravel + React.
- [ ] Laravel Pulse.
- [ ] Telescope (protegido, solo dev/staging).
- [ ] Revisar logging.
- [ ] Política de datos sensibles en logs.

## Sprint 2 — Health

- [ ] `StatusService`.
- [ ] Check aplicación.
- [ ] Check DB.
- [ ] Check Redis/cache.
- [ ] Check queue.
- [ ] Checks funcionales de Pedales.

## Sprint 3 — Admin

- [ ] `/admin/observabilidad`.
- [ ] Resumen de salud.
- [ ] Métricas técnicas.
- [ ] Métricas de negocio.
- [ ] Errores recientes.
- [ ] Jobs fallidos.

## Sprint 4 — Incidencias

- [ ] Modelo de incidencias.
- [ ] CRUD/admin.
- [ ] Estados y severidades.
- [ ] Histórico.
- [ ] Servicios afectados.

## Sprint 5 — Público

- [ ] `/estado` con Inertia.
- [ ] Estado general.
- [ ] Estado por servicio.
- [ ] Incidencias activas.
- [ ] Histórico.
- [ ] Diseño coherente con Pedales.
- [ ] Revisión de información expuesta.

## Sprint 6 — Alertas y uptime

- [ ] Alertas críticas.
- [ ] Monitorización externa.
- [ ] Pruebas de caída.
- [ ] Pruebas de recuperación.
- [ ] Procedimiento de respuesta.

# Consideraciones Railway

| Variable | Valor |
|---|---|
| `CACHE_STORE` | `redis` |
| `QUEUE_CONNECTION` | `redis` |
| `REDIS_URL` | Railway lo provee automáticamente |
| `SENTRY_LARAVEL_DSN` | Añadir en Railway secrets |
| `TELESCOPE_ENABLED` | `false` en producción |

# Evolución futura

No introducir inicialmente salvo necesidad real:
- OpenTelemetry.
- Grafana.
- Prometheus.
- Loki.
- Tempo.

Mantener la arquitectura sencilla mientras la escala no lo requiera.

# Criterio final de éxito

Antes de abrir Pedales al público en 2027 debe ser posible responder rápidamente:

1. ¿Está funcionando Pedales?
2. ¿Qué parte está fallando?
3. ¿Desde cuándo?
4. ¿Cuántos usuarios están afectados?
5. ¿Es técnico o funcional?
6. ¿Qué cambió recientemente?
7. ¿Hay jobs bloqueados?
8. ¿Funcionan predicciones y resultados?
9. ¿Existe una incidencia activa?
10. ¿Qué información debemos mostrar a los usuarios?

> **Objetivo: si Pedales falla a las 03:00, saberlo antes que los usuarios y tener suficiente información para diagnosticarlo rápidamente.**
