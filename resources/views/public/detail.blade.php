@extends('layouts.public')

@section('content')
    <main>
        <section class="bg-cream">
            <div class="mx-auto grid max-w-7xl gap-10 px-5 py-14 lg:grid-cols-[1fr_0.9fr] lg:items-center lg:px-8 lg:py-20">
                <div class="max-w-2xl">
                    <a href="{{ $kind === 'service' ? route('services.index') : ($kind === 'package' ? route('packages.index') : route('gallery.index')) }}" class="text-xs font-semibold uppercase tracking-[0.15em] text-forest">← Back to {{ $kind === 'portfolio' ? 'gallery' : str_replace('_', ' ', $kind).'s' }}</a>
                    <p class="eyebrow mt-8">{{ $entry->category ?? ucfirst(str_replace('_', ' ', $kind)) }}</p>
                    <h1 class="mt-4 font-display text-5xl leading-tight sm:text-6xl">{{ $entry->title }}</h1>
                    @if ($entry->excerpt)<p class="mt-6 text-lg leading-8 text-ink/65">{{ $entry->excerpt }}</p>@endif
                    @if ($kind === 'package')<p class="mt-5 text-sm font-semibold text-clay">{{ $entry->metadata['price_label'] ?? 'Pricing is confirmed in a tailored quotation.' }}</p>@endif
                    @if (in_array($kind, ['service', 'package'], true))
                        <a href="{{ route('booking.create', $kind === 'service' ? ['service' => $entry->id] : ['package' => $entry->id]) }}" class="button-primary mt-8">Request a quote <span aria-hidden="true">↗</span></a>
                    @endif
                </div>
                <div class="aspect-[5/4] overflow-hidden bg-mist">
                    <img class="h-full w-full object-cover" src="{{ $entry->image_path ? asset('storage/'.$entry->image_path) : 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1300&q=85' }}" alt="{{ $entry->metadata['alt_text'] ?? $entry->title }}" fetchpriority="high">
                </div>
            </div>
        </section>
        <section class="mx-auto grid max-w-7xl gap-12 px-5 py-14 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:px-8 lg:py-20">
            <aside class="h-fit border-l-2 border-gold pl-6">
                <p class="eyebrow">A note on planning</p>
                <p class="mt-3 font-display text-2xl leading-9">Every detail is confirmed around your date, guests and venue.</p>
            </aside>
            <div class="max-w-3xl whitespace-pre-line text-base leading-8 text-ink/75">{{ $entry->body }}</div>
        </section>
    </main>
@endsection
