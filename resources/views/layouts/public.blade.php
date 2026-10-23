<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $metaDescription ?? $siteSettings['tagline'] ?? 'Thoughtful catering and event planning for gatherings worth remembering.' }}">
    <meta name="theme-color" content="#0b3d2e">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ $pageTitle ?? ($siteSettings['business_name'] ?? config('app.name')) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
    <body class="bg-paper text-ink antialiased">
    @if ($siteSettings['show_demo_notice'] ?? false)
        <div class="border-b border-gold/70 bg-forest px-4 py-2.5 text-center text-[11px] font-medium tracking-wide text-white/90">
            {{ $siteSettings['demo_notice'] ?? 'Starter content — confirm company details before launch.' }}
        </div>
    @endif
    <header class="sticky top-0 z-40 border-b border-ink/10 bg-paper/95 shadow-[0_8px_24px_-24px_rgba(10,50,36,0.6)] backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-4 lg:px-8">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3" aria-label="{{ $siteSettings['business_name'] ?? config('app.name') }} home">
                <x-brand-mark mark-size="h-12 w-12" descriptor="Rawalpindi · Catering & events" />
            </a>
            <nav class="hidden items-center gap-6 text-[13px] font-medium text-ink/75 xl:flex" aria-label="Main navigation">
                <a class="transition hover:text-clay" href="{{ route('services.index') }}">Services</a>
                <a class="transition hover:text-clay" href="{{ route('menus.index') }}">Menus</a>
                <a class="transition hover:text-clay" href="{{ route('packages.index') }}">Packages</a>
                <a class="transition hover:text-clay" href="{{ route('gallery.index') }}">Gallery</a>
                <a class="transition hover:text-clay" href="{{ route('about') }}">Our approach</a>
                <a class="transition hover:text-clay" href="{{ route('contact') }}">Contact</a>
            </nav>
            <div class="hidden items-center gap-4 lg:flex">
                @if (! empty($siteSettings['whatsapp_url']))
                    <a class="hidden text-xs font-semibold text-forest underline decoration-gold underline-offset-4 2xl:inline" href="{{ $siteSettings['whatsapp_url'] }}" target="_blank" rel="noopener">WhatsApp ↗</a>
                @endif
                <a class="button-primary" href="{{ route('booking.create') }}">Plan your event <span aria-hidden="true">↗</span></a>
            </div>
            <button type="button" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu" class="grid h-10 w-10 place-items-center rounded-lg border border-ink/15 text-xl text-forest lg:hidden" aria-label="Toggle navigation">
                <span aria-hidden="true">☰</span><span class="sr-only">Toggle navigation</span>
            </button>
        </div>
        <nav id="mobile-menu" data-mobile-menu class="hidden border-t border-ink/10 bg-paper px-5 py-5 shadow-lg lg:hidden" aria-label="Mobile navigation">
            <div class="mx-auto grid max-w-7xl gap-1 text-sm">
                <a class="rounded-lg px-3 py-2.5 hover:bg-cream" href="{{ route('services.index') }}">Services</a>
                <a class="rounded-lg px-3 py-2.5 hover:bg-cream" href="{{ route('menus.index') }}">Menus</a>
                <a class="rounded-lg px-3 py-2.5 hover:bg-cream" href="{{ route('packages.index') }}">Packages</a>
                <a class="rounded-lg px-3 py-2.5 hover:bg-cream" href="{{ route('gallery.index') }}">Gallery</a>
                <a class="rounded-lg px-3 py-2.5 hover:bg-cream" href="{{ route('about') }}">Our approach</a>
                <a class="rounded-lg px-3 py-2.5 hover:bg-cream" href="{{ route('faqs') }}">FAQs</a>
                <a class="rounded-lg px-3 py-2.5 hover:bg-cream" href="{{ route('contact') }}">Contact</a>
                @if (! empty($siteSettings['whatsapp_url']))<a class="rounded-lg px-3 py-2.5 font-semibold text-forest" href="{{ $siteSettings['whatsapp_url'] }}" target="_blank" rel="noopener">Message on WhatsApp ↗</a>@endif
                <a class="mt-2 rounded-md bg-forest px-4 py-3.5 text-center font-semibold text-white shadow-sm" href="{{ route('booking.create') }}">Plan your event</a>
            </div>
        </nav>
    </header>

    @if (session('success'))
        <div class="mx-auto mt-5 max-w-7xl px-5 lg:px-8"><p role="status" class="rounded-sm border border-forest/15 bg-mist px-4 py-3 text-sm text-forest">{{ session('success') }}</p></div>
    @endif
    @yield('content')

    <footer class="bg-ink text-white">
        <div class="mx-auto grid max-w-7xl gap-12 px-5 py-16 md:grid-cols-4 lg:px-8">
            <div class="md:col-span-2">
                <x-brand-mark inverse mark-size="h-14 w-14" descriptor="Rawalpindi · Catering & events" />
                <p class="mt-5 max-w-md text-sm leading-7 text-white/70">{{ $siteSettings['tagline'] ?? 'Catering and event planning for celebrations in Rawalpindi.' }}</p>
                @if (! empty($siteSettings['address']))<p class="mt-5 text-xs leading-6 text-white/55">{{ $siteSettings['address'] }}</p>@endif
                @if (! empty($siteSettings['city']))<a class="mt-2 inline-block text-xs text-white/65 underline decoration-gold underline-offset-4" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode(trim($siteSettings['city']).', Pakistan') }}" target="_blank" rel="noopener">{{ $siteSettings['city'] }}, Pakistan · Open city map ↗</a>@endif
            </div>
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-gold">Explore</h2>
                <ul class="mt-5 grid gap-3 text-sm text-white/70">
                    <li><a href="{{ route('services.index') }}" class="hover:text-white">Services</a></li>
                    <li><a href="{{ route('packages.index') }}" class="hover:text-white">Packages</a></li>
                    <li><a href="{{ route('gallery.index') }}" class="hover:text-white">Gallery</a></li>
                    <li><a href="{{ route('faqs') }}" class="hover:text-white">FAQs</a></li>
                </ul>
            </div>
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-gold">Start a conversation</h2>
                <ul class="mt-5 grid gap-3 text-sm text-white/70">
                    @if (! empty($siteSettings['phone'])) <li><a href="tel:{{ $siteSettings['phone'] }}" class="hover:text-white">{{ $siteSettings['phone'] }}</a></li> @endif
                    @if (! empty($siteSettings['email'])) <li><a href="mailto:{{ $siteSettings['email'] }}" class="hover:text-white">{{ $siteSettings['email'] }}</a></li> @endif
                    @if (! empty($siteSettings['whatsapp_url'])) <li><a href="{{ $siteSettings['whatsapp_url'] }}" target="_blank" rel="noopener" class="hover:text-white">WhatsApp ↗</a></li> @endif
                    <li><a href="{{ route('contact') }}" class="hover:text-white">Contact the team</a></li>
                    <li><a href="{{ route('booking.create') }}" class="hover:text-white">Request a quote</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-5 text-xs text-white/50 sm:flex-row sm:items-center sm:justify-between lg:px-8">
                <p>© {{ now()->year }} {{ $siteSettings['business_name'] ?? config('app.name') }}. All rights reserved.</p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('policies.show', 'privacy-policy') }}" class="hover:text-white">Privacy</a>
                    <a href="{{ route('policies.show', 'terms') }}" class="hover:text-white">Terms</a>
                    <a href="{{ route('policies.show', 'booking-policy') }}" class="hover:text-white">Booking policy</a>
                    <a href="{{ route('policies.show', 'cancellation-policy') }}" class="hover:text-white">Cancellation</a>
                    <a href="{{ route('login') }}" class="hover:text-white">Staff sign in</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
