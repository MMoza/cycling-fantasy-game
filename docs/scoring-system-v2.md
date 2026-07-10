# Sistema de Puntuación v2 — Pedales

> **Estado**: Diseñado, pendiente de implementación
> **Versión**: 1.0
> **Temporada objetivo**: 2027

## Visión

Nuevo sistema de puntuación oficial para competiciones de Pedales, diseñado para ser accesible pero con profundidad estratégica. Las Grandes Vueltas son el evento principal, pero Monumentos y vueltas de una semana tienen peso significativo.

## Principios de diseño

1. Sistema accesible para jugadores nuevos
2. Premiar el conocimiento sin penalizar en exceso los pequeños errores
3. Las Grandes Vueltas son el evento principal de la temporada
4. Las etapas mantienen viva la clasificación
5. Las clasificaciones finales premian la estrategia a largo plazo
6. Los Monumentos deben tener un gran peso en la temporada
7. Las vueltas de una semana se sitúan entre un Monumento y una Gran Vuelta
8. Las clásicas menores siguen siendo relevantes sin eclipsar el resto

## Grandes Vueltas

**Eventos**: Tour de Francia, Giro de Italia, Vuelta a España

### Predicciones por etapa

| Dificultad | Ganador | 2º | 3º | Podio desordenado |
|------------|---------|----|----|-------------------|
| 1 (llana/sprint) | 15 | 10 | 5 | 3 |
| 2 (media montaña) | 20 | 15 | 10 | 6 |
| 3 (alta montaña/TT) | 25 | 18 | 12 | 8 |

### Clasificaciones diarias

| Clasificación | Puntos |
|---------------|--------|
| General | 10 |
| Puntos (verde) | 6 |
| Montaña | 6 |
| Joven (blanco) | 6 |
| Equipos | 5 |

### Clasificaciones finales

**General**: 1º=100, 2º=75, 3º=60, 4º=45, 5º=35 | Desordenado=15
**Puntos/Montaña/Joven**: 1º=35, 2º=25, 3º=18 | Desordenado=10
**Equipos**: Ganador=30

## Vueltas de una semana

**Eventos**: París-Niza, Tirreno-Adriático, Volta a Catalunya, Itzulia, Tour de Romandía, Critérium du Dauphiné, Tour de Suiza, UAE Tour

### Predicciones por etapa

| Dificultad | Ganador | 2º | 3º | Podio desordenado |
|------------|---------|----|----|-------------------|
| 1 | 15 | 10 | 5 | 3 |
| 2 | 20 | 15 | 10 | 6 |
| 3 | 25 | 18 | 12 | 8 |

### Clasificaciones diarias

| Clasificación | Puntos |
|---------------|--------|
| General | 8 |
| Puntos | 5 |
| Montaña | 5 |
| Joven | 5 |
| Equipos | 4 |

### Clasificaciones finales

**General**: 1º=70, 2º=55, 3º=40, 4º=30, 5º=20 | Desordenado=10
**Puntos/Montaña/Joven**: 1º=30, 2º=20, 3º=15 | Desordenado=8
**Equipos**: Ganador=20

## Monumentos

**Eventos**: Milán-San Remo, Tour de Flandes, París-Roubaix, Lieja-Bastoña-Lieja, Il Lombardia

### Predicción Top 10

| Posición | Ordenado | Desordenado |
|----------|----------|-------------|
| 1º | 80 | 20 |
| 2º | 60 | 18 |
| 3º | 50 | 16 |
| 4º | 40 | 14 |
| 5º | 35 | 12 |
| 6º | 30 | 10 |
| 7º | 25 | 8 |
| 8º | 20 | 6 |
| 9º | 15 | 5 |
| 10º | 10 | 4 |

**Bonus**: Perfect Top 10 = +100 puntos

## Clásicas de un día

**Eventos**: Strade Bianche, E3 Saxo Classic, A Través de Flandes, Giro dell'Emilia, Tre Valli Varesine, GP Québec, GP Montréal

### Predicción Top 4

| Posición | Ordenado | Desordenado |
|----------|----------|-------------|
| 1º | 50 | 12 |
| 2º | 35 | 10 |
| 3º | 25 | 8 |
| 4º | 18 | 6 |

**Bonus**: Perfect Top 4 = +40 puntos

## Reglas globales

- **No se predicen**: Combativo de etapa ni Supercombativo final
- El podio desordenado premia acertar los 3 primeros sin importar el orden
- Los bonus de "perfecto" premian la precisión total

---

# Sistema de Badges

## Badges visibles

| Badge | Criterio |
|-------|----------|
| Cazador de Etapas | Acertar X ganadores de etapa |
| Maestro del Podio | Acertar X podios completos |
| Oráculo Amarillo | Acertar ganador de la General |
| Rey Verde | Acertar ganador de Puntos |
| Rey de la Montaña | Acertar ganador de Montaña |
| Joven Promesa | Acertar ganador de Joven |
| Estratega | Mejor puntuación en una liga |
| Campeón | Ganar una liga oficial |
| Podios | X podios en ligas |
| Top 10 | X veces en Top 10 |
| Campeón de Temporada | 1º en clasificación de temporada |
| Podios de Temporada | Top 3 en temporada |
| Top 10 de Temporada | Top 10 en temporada |
| Participante | Unirse a una liga |
| Veterano | Participar en X temporadas |
| Siempre Presente | No perderse ninguna etapa |
| Puntos Históricos | Acumular X puntos totales |
| Etapas Jugadas | Participar en X etapas |
| Farolillo Rojo | Última posición en una liga |

## Badges ocultos

| Badge | Criterio |
|-------|----------|
| Coleccionista | Acertar todas las clasificaciones de una GV |
| Triple Corona | Ganar Tour, Giro y Vuelta en misma temporada |
| Cinco Monumentos | Acertar ganador de los 5 Monumentos |
| Perfecto | Top 10 perfecto en un Monumento |
| Adivino | Acertar podio desordenado en X etapas consecutivas |

## Badge especial

**Tester Fundador** (exclusivo)
- Participó en la primera beta pública durante el Tour de Francia 2026

---

## JSON de referencia

El sistema completo está definido en `docs/scoring-system-v2.json` para uso programático.
