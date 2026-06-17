# ADR-0006: Design System minimalista neutro estilo Vercel

## Estado
Accepted

## Contexto
El proyecto necesita una interfaz fluida, moderna y entendible con una imagen de marca consistente.
Se requiere un diseño system que:
- Sea neutral y no compita con el contenido (datos ciclistas)
- Funcione bien en móvil y desktop
- Sea fácil de mantener y extender
- Tenga soporte dark mode desde el inicio

## Decisión
Adoptar un diseño system minimalista y limpio estilo Vercel:
- **Colores**: Paleta neutra zinc como base (no colores de marca específicos aún)
- **Tipografía**: Inter (default de shadcn/ui)
- **Estilo**: Minimalista, limpio, con mucho espacio en blanco
- **Iconografía**: Lucide React (incluido con shadcn)
- **Componentes**: shadcn/ui como base, sin componentes custom innecesarios
- **Dark mode**: Soportado desde el día uno con Tailwind `dark:` variant

El logo, favicon y Open Graph image se definirán antes de comenzar el frontend (Fase 5).

## Alternativas consideradas
- **Colores temáticos del Tour (amarillo/negro)**: Rechazado porque el proyecto soportará Giro y Vuelta en v2.
- **Estilo deportivo/energético con gradientes**: Demasiado pesado para una app de datos.
- **Tailwind UI o componentes premium**: Innecesario, shadcn cubre todas las necesidades.

## Consecuencias
### Positivas
- Interfaz profesional y limpia
- Fácil de mantener
- Dark mode nativo
- shadcn/ui tiene excelente documentación y comunidad
- Lucide icons son consistentes y ligeros

### Negativas
- Aspecto genérico hasta que se defina logo/branding
- Se necesitará trabajo de diseño adicional para diferenciarse
- Los colores neutros pueden sentirse "fríos" para una app deportiva
