@extends('layouts.public')

@section('content')
    <main>
        <section class="bg-cream">
            <div class="mx-auto max-w-7xl px-5 py-16 lg:px-8 lg:py-24">
                <p class="eyebrow">{{ $siteSettings['business_name'] ?? config('app.name') }}</p>
                <h1 class="mt-4 max-w-3xl font-display text-5xl leading-tight sm:text-6xl">{{ $title }}</h1>
                <p class="mt-5 max-w-2xl text-base leading-8 text-ink/65">{{ $intro }}</p>
            </div>
        </section>
        <section class="mx-auto max-w-7xl px-5 py-14 lg:px-8 lg:py-20">
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($items as $item)
                    @php
                        $detailRoute = match ($kind) {
                            'service' => route('services.show', $item->slug),
                            'package' => route('packages.show', $item->slug),
                            'portfolio' => route('gallery.show', $item->slug),
                            default => null,
                        };
                    @endphp
                    <article class="flex flex-col overflow-hidden border border-ink/10 bg-white">
                        @if ($kind !== 'menu_item')
                            <div class="aspect-[4/3] overflow-hidden bg-cream">
                                <img class="h-full w-full object-cover" src="{{ $item->image_path ? asset('storage/'.$item->image_path) : 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=900&q=80' }}" alt="{{ $item->metadata['alt_text'] ?? $item->title }}" loading="lazy">
                            </div>
                        @endif
                        <div class="flex flex-1 flex-col p-6">
                            <p class="eyebrow">{{ $item->category ?? ucfirst(str_replace('_', ' ', $kind)) }}</p>
                            <h2 class="mt-3 font-display text-2xl">{{ $item->title }}</h2>
                            <p class="mt-3 flex-1 text-sm leading-6 text-ink/65">{{ $item->excerpt ?? Str::limit(strip_tags($item->body), 160) }}</p>
                            @if ($kind === 'package')
                                <p class="mt-5 text-sm font-medium text-clay">{{ $item->metadata['price_label'] ?? 'Ask us for a tailored quote' }}</p>
                            @elseif ($kind === 'menu_item' && ! empty($item->metadata['dietary_labels']))
                                <p class="mt-5 text-xs text-forest">{{ implode(' · ', (array) $item->metadata['dietary_labels']) }}</p>
                            @endif
                            @if ($detailRoute)
                                <a class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-forest underline decoration-gold underline-offset-8" href="{{ $detailRoute }}">Explore details <span aria-hidden="true">→</span></a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="border border-dashed border-ink/20 p-9 text-sm leading-7 text-ink/60 sm:col-span-2 lg:col-span-3">
                        There is no published content here yet. <a href="{{ route('contact') }}" class="font-semibold text-forest underline">Contact the team</a> to talk through your event.
                    </div>
                @endforelse
            </div>
            <div class="mt-10">{{ $items->links() }}</div>
        </section>
    </main>
@endsection
