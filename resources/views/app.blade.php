<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- SEO -->
        <meta name="description" content="Pedales es el fantasy de ciclismo para Grandes Vueltas. Crea tu liga, pronostica el Top 5, maillots, ganadores de etapa y compite con tus amigos.">
        <meta name="keywords" content="fantasy cycling, ciclismo fantasy, tour de francia, giro de italia, la vuelta, porras ciclismo, juego ciclismo, liga ciclismo">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ config('app.url') }}">

        <!-- Open Graph -->
        <meta property="og:title" content="Pedales — Fantasy Cycling">
        <meta property="og:description" content="Crea tu liga, pronostica el Top 5, maillots y ganadores de etapa. Compite con tus amigos en las Grandes Vueltas del ciclismo.">
        <meta property="og:image" content="{{ config('app.url') }}/og-image.svg">
        <meta property="og:url" content="{{ config('app.url') }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Pedales Fantasy Cycling">
        <meta property="og:locale" content="es_ES">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Pedales — Fantasy Cycling">
        <meta name="twitter:description" content="Crea tu liga, pronostica el Top 5, maillots y ganadores de etapa. Compite con tus amigos en las Grandes Vueltas del ciclismo.">
        <meta name="twitter:image" content="{{ config('app.url') }}/og-image.svg">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
