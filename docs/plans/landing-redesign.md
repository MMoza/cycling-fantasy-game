# Plan: Rediseño Landing Page — "Wow Factor"

## Rama

`feat/landing-redesign`

## Resumen

Rediseñar la landing de Pedales con: hero animado + competición activa/conteo regresivo, secciones con animación al scroll (Framer Motion), "Cómo funciona" con 4 pasos, "Por qué Pedales" con features, footer completo con calendario UCI, y clasificación pública de temporada.

## Stack a añadir

| Paquete | Tamaño | Uso |
|---|---|---|
| `framer-motion` | ~4KB gzipped | Animaciones de scroll (fade-in, slide-up) |

## Archivos a crear

| Archivo | Descripción |
|---|---|
| `resources/js/components/Footer.tsx` | Footer completo con calendario UCI, links, about/contact |
| `resources/js/components/ScrollReveal.tsx` | Wrapper Framer Motion para animaciones on scroll |
| `resources/js/components/LandingHero.tsx` | Hero animado con countdown |
| `resources/js/components/HowItWorks.tsx` | Sección "Cómo funciona" (4 pasos) |
| `resources/js/components/WhyPedales.tsx` | Sección "Por qué Pedales" |
| `resources/js/components/SeasonLeaderboard.tsx` | Clasificación pública temporada |
| `app/Presentation/Http/Controllers/PublicSeasonClassificationController.php` | API pública classification |
| Ruta pública `GET /api/public/season-classification` | Endpoint sin auth |

## Archivos a modificar

| Archivo | Cambio |
|---|---|
| `resources/js/Pages/Landing.tsx` | Reescribir usando nuevos componentes |
| `resources/js/Layouts/LandingLayout.tsx` | Importar Footer.tsx, quitar footer inline, header con auth check |
| `resources/css/app.css` | Añadir keyframes para hero si necesario |
| `app/Presentation/Http/Controllers/LandingController.php` | Pasar datos de competición activa/próxima + auth user |
| `resources/js/components/Countdown.tsx` | Crear componente countdown reutilizable |

---

## Detalle por sección

### 1. Hero animado + countdown

**`LandingHero.tsx`**

- Background image (`/portada-landing.avif`) con overlay gradiente
- Logo + título con animación de entrada (fade-in + scale desde 0.8 → 1)
- Tagline aparece 0.3s después del título (stagger)
- Botones CTA aparecen 0.3s después del tagline
- **Debajo del CTA**: badge dinámico:
  - Si hay competición ongoing → `"🟢 {CompetitionName} {Year} — En curso"`
  - Si no hay ongoing pero sí upcoming → `"Próximo: {CompetitionName} {Year}"` + countdown `Xd Xh Xm Xs`
  - Si no hay ninguna → ocultar badge

**Datos del controller** (`LandingController`):

```php
$now = now();
$ongoing = EditionModel::where('status', 'ongoing')
    ->with('competition')
    ->first();
$upcoming = !$ongoing ? EditionModel::where('status', 'upcoming')
    ->where('start_date', '>', $now)
    ->orderBy('start_date')
    ->with('competition')
    ->first() : null;

return Inertia::render('Landing', [
    'auth' => [
        'user' => $request->user() ? [
            'id' => $request->user()->id,
            'name' => $request->user()->name,
        ] : null,
    ],
    'activeEdition' => $ongoing ? [
        'name' => $ongoing->competition->name . ' ' . $ongoing->year,
        'status' => 'ongoing',
    ] : null,
    'nextEdition' => $upcoming ? [
        'name' => $upcoming->competition->name . ' ' . $upcoming->year,
        'startDate' => $upcoming->start_date->toIso8601String(),
    ] : null,
]);
```

**`Countdown.tsx`** — componente reutilizable:

- Recibe `targetDate: string` (ISO 8601)
- Muestra `Xd Xh Xm Xs` con actualización cada segundo
- Usa `useState` + `useEffect` + `setInterval`
- Stop en 0

### Header dinámico según auth

**`LandingLayout.tsx`** — el header cambia según si el usuario tiene sesión:

- **No logueado**: muestra "Iniciar sesión" + "Registrarse" (actual)
- **Logueado**: muestra "Jugar" (link a `/dashboard`) en vez de "Iniciar sesión", y se oculta "Registrarse"

El `LandingController` pasa el user autenticado (o null):

```php
return Inertia::render('Landing', [
    'auth' => [
        'user' => $request->user() ? [
            'id' => $request->user()->id,
            'name' => $request->user()->name,
        ] : null,
    ],
    'activeEdition' => ...,
    'nextEdition' => ...,
]);
```

En `LandingLayout.tsx`:

```tsx
{auth?.user ? (
    <Link href={route('dashboard')} className="inline-flex h-9 items-center ...">
        Jugar
    </Link>
) : (
    <>
        <Link href={route('login')} ...>Iniciar sesión</Link>
        <Link href={route('register')} ...>Registrarse</Link>
    </>
)}
```

### 2. Secciones con scroll animation

**`ScrollReveal.tsx`** — wrapper genérico:

```tsx
import { motion } from 'framer-motion';

export function ScrollReveal({ children, className, delay = 0 }) {
    return (
        <motion.div
            initial={{ opacity: 0, y: 30 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: '-50px' }}
            transition={{ duration: 0.6, delay, ease: 'easeOut' }}
            className={className}
        >
            {children}
        </motion.div>
    );
}
```

Se usa en todas las secciones: `<ScrollReveal>` envuelve cada sección o cada card individual con `delay` escalonado.

### 3. "Cómo funciona" — 4 pasos

**`HowItWorks.tsx`**

1. **Registrate** — Icono `UserPlus` — "Crea tu cuenta en segundos con Google o email"
2. **Únete a cualquier competición UCI World Tour** — Icono `Trophy` — "Desde el Tour de Francia hasta las clásicas monumento"
3. **Haz tus pronósticos** — Icono `Target` — "Antes de cada carrera y etapa, elige tus favoritos"
4. **Compite contra la comunidad** — Icono `TrendingUp` — "Sube en la clasificación y demuestra tu conocimiento del ciclismo"

Layout: grid 4 columnas (desktop), 2x2 (tablet), 1 columna (mobile). Cada paso tiene:
- Número grande semitransparente de fondo (01, 02, 03, 04)
- Icono circular con color de brand
- Título + descripción
- Línea conectora entre pasos (solo desktop)

Cada card se anima individualmente con `ScrollReveal` y `delay` progresivo (0, 0.1, 0.2, 0.3).

### 4. "Por qué Pedales" — features

**`WhyPedales.tsx`**

1. **Todo el calendario World Tour** — Icono `Calendar` — "Grandes Vueltas, clásicas, championships. Todo el ciclismo profesional en un solo sitio."
2. **Clasificación por competición** — Icono `BarChart3` — "Compite en cada carrera por separado. Maillots, etapas y clasificaciones."
3. **Clasificación de la temporada** — Icono `Trophy` — "Puntos acumulados todo el año. El verdadero campeón de Pedales."
4. **Próximamente: Ligas privadas** — Icono `Users` — "Crea tu liga con amigos, elige tu sistema de puntuación y compite en tu grupo." (con badge "Próximamente")

Layout: grid 2 columnas (desktop), 1 columna (mobile). Cards con borde sutil, hover shadow.

Se anima igual con `ScrollReveal`.

### 5. Clasificación pública de temporada

**`SeasonLeaderboard.tsx`**

- Sección entre "Por qué Pedales" y CTA final
- Título: "Clasificación de la temporada"
- Subtítulo: "Los mejores pronosticadores de Pedales"
- Tabla/lista con top 5-10 usuarios
  - Posición (#1, #2, #3...)
  - Avatar + nombre
  - Puntos totales
- Badge "Inicia sesión para ver tu posición" al final
- Si la API retorna empty → ocultar toda la sección

**API pública** (`GET /api/public/season-classification`):

```php
// PublicSeasonClassificationController.php
public function __invoke(): JsonResponse
{
    // Suma de score_events por usuario (sin stage_id = pre-race, con stage_id = stage)
    // Top 10 ordenados por total_points DESC
    // Retorna: [{position, user_name, avatar, total_points}]
}
```

Sin middleware auth. Cache de 5 minutos.

### 6. Footer completo

**`Footer.tsx`** — componente independiente

Estructura en 4 columnas (desktop), apilado en mobile:

**Columna 1 — Pedales**
- Logo + "Pedales Fantasy Cycling"
- Breve descripción
- Links: Clasificación temporada, Sobre nosotros, Contacto, Política de cookies

**Columna 2 — Grand Tours + Major Tours**
- Grand Tours: Tour de Francia, Giro d'Italia, Vuelta a España
- Major Tours: Paris-Nice, Tirreno-Adriatico, Volta a Catalunya, Itzulia Basque Country, Tour de Romandie, Tour de Suisse, Tour Auvergne-Rhône-Alpes

**Columna 3 — Monuments + Championships**
- Monuments: Milano-SanRemo, Ronde van Vlaanderen, Paris-Roubaix, Liège-Bastogne-Liège, Il Lombardia
- Championships: World Championships, European Championships

**Columna 4 — Top Classics**
- Omloop Het Nieuwsblad, Strade Bianche, E3 Classic, Gent-Wevelgem, Dwars door Vlaanderen, Eschborn-Frankfurt, Amstel Gold Race, La Flèche Wallonne, San Sebastian, Bretagne Classic, GP Québec, GP Montréal

**Bottom bar**: Copyright + "Hecho con ❤️ para los aficionados al ciclismo"

### 7. Landing.tsx reescrito

```tsx
export default function Landing({ auth, activeEdition, nextEdition }) {
    return (
        <LandingLayout auth={auth}>
            <Head>...</Head>
            <LandingHero activeEdition={activeEdition} nextEdition={nextEdition} />
            <MarqueeSection />  {/* se mantiene el carousel de Grand Tours */}
            <HowItWorks />
            <WhyPedales />
            <SeasonLeaderboard />
            <CTASection />  {/* se mantiene el CTA final */}
            <Footer />
        </LandingLayout>
    );
}
```

---

## Orden de implementación

```
1.  Instalar framer-motion
2.  Crear ScrollReveal.tsx (wrapper animación)
3.  Crear Countdown.tsx (countdown regresivo)
4.  Actualizar LandingController (pasar datos competición + auth user)
5.  Actualizar LandingLayout.tsx (header dinámico auth + Footer)
6.  Crear LandingHero.tsx (hero animado + countdown)
7.  Crear HowItWorks.tsx (4 pasos)
8.  Crear WhyPedales.tsx (4 features)
9.  Crear API pública season-classification
10. Crear SeasonLeaderboard.tsx
11. Crear Footer.tsx (calendario UCI completo)
12. Reescribir Landing.tsx (integrar todo)
13. Añadir keyframes en app.css si necesario
14. Verificar: npm run build + test manuales
```

## Resultado esperado

| Elemento | Antes | Después |
|---|---|---|
| Header | Siempre Login/Register | "Jugar" si logueado, Login/Register si no |
| Hero | Estático | Animación entrada + countdown competición |
| Secciones | Sin animación | Fade-in + slide-up al scroll |
| "Cómo funciona" | 3 pasos genéricos | 4 pasos específicos (registrar → competir) |
| "Por qué Pedales" | 4 features genéricos | 4 features específicos + "Próximamente" |
| Clasificación | No existía | Top 10 pública temporada |
| Footer | 1 línea copyright | Calendario UCI completo + links |
| Dependencies | 0 nuevas | +1 (framer-motion) |
