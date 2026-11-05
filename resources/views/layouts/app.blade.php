<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('ziswaf.org.name')) — Zakat, Infaq, Sedekah &amp; Wakaf</title>
    <meta name="description" content="@yield('description', 'Salurkan zakat, infaq, sedekah, dan wakaf Anda secara aman dan transparan.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-800 antialiased">
<header class="sticky top-0 z-30 border-b border-stone-200 bg-white/90 backdrop-blur" x-data="{ open: false }">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
        <a href="{{ route('home') }}" class="text-lg font-semibold tracking-tight text-emerald-800">{{ config('ziswaf.org.name') }}</a>
        <nav class="hidden items-center gap-6 text-sm font-medium md:flex">
            <a href="{{ route('home') }}" class="hover:text-emerald-700">Kampanye</a>
            <a href="{{ route('zakat') }}" class="hover:text-emerald-700">Kalkulator Zakat</a>
            <a href="{{ route('donation.create') }}" class="rounded-full bg-emerald-700 px-4 py-2 text-white hover:bg-emerald-800">Donasi Sekarang</a>
        </nav>
        <button class="md:hidden" @click="open = !open" aria-label="Menu">☰</button>
    </div>
    <nav x-show="open" x-cloak class="space-y-2 border-t px-4 py-3 text-sm md:hidden">
        <a class="block" href="{{ route('home') }}">Kampanye</a>
        <a class="block" href="{{ route('zakat') }}">Kalkulator Zakat</a>
        <a class="block font-semibold text-emerald-700" href="{{ route('donation.create') }}">Donasi Sekarang</a>
    </nav>
</header>

<main>@yield('content')</main>

<footer class="mt-16 border-t border-stone-200 bg-white">
    <div class="mx-auto max-w-6xl px-4 py-8 text-sm text-stone-500">
        <p class="font-medium text-stone-700">{{ config('ziswaf.org.name') }}</p>
        <p>{{ config('ziswaf.org.address') }}</p>
        <p class="mt-2">© {{ date('Y') }} — Setiap donasi tercatat dan dilaporkan penyalurannya.</p>
    </div>
</footer>
<style>[x-cloak]{display:none!important}</style>
</body>
</html>
