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

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">

        <!-- Open Graph -->
        <meta property="og:title" content="Pedales — Fantasy Cycling">
        <meta property="og:description" content="Crea tu liga, pronostica el Top 5, maillots y ganadores de etapa. Compite con tus amigos en las Grandes Vueltas del ciclismo.">
        <meta property="og:image" content="{{ config('app.url') }}/portada-landing.avif">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:url" content="{{ config('app.url') }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Pedales Fantasy Cycling">
        <meta property="og:locale" content="es_ES">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Pedales — Fantasy Cycling">
        <meta name="twitter:description" content="Crea tu liga, pronostica el Top 5, maillots y ganadores de etapa. Compite con tus amigos en las Grandes Vueltas del ciclismo.">
        <meta name="twitter:image" content="{{ config('app.url') }}/portada-landing.avif">

        <!-- PWA -->
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#18181b">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Pedales">
        <link rel="apple-touch-icon" href="/icons/icon-192.svg">
        <link rel="mask-icon" href="/icons/icon-maskable.svg" color="#18181b">

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
