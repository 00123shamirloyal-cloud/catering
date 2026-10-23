@extends('layouts.public')

@section('content')
    <main>
        <section class="bg-cream">
            <div class="mx-auto grid max-w-7xl gap-12 px-5 py-16 lg:grid-cols-2 lg:items-center lg:px-8 lg:py-24">
                <div><p class="eyebrow">Daata · Rawalpindi</p><h1 class="mt-4 font-display text-5xl leading-tight sm:text-6xl">A gathering should feel like yours.</h1><p class="mt-6 max-w-xl text-base leading-8 text-ink/65">{{ $page?->excerpt ?? 'Daata Catering and Event Organizer is based in Rawalpindi, Pakistan. Tell us about your event and we can discuss menu and planning options for your occasion.' }}</p></div>
                <div class="aspect-[5/4] overflow-hidden bg-mist"><img class="h-full w-full object-cover" src="{{ $page?->image_path ? asset('storage/'.$page->image_path) : 'https://images.unsplash.com/photo-1529636798458-92182e662485?auto=format&fit=crop&w=1300&q=85' }}" alt="A welcoming dinner table set for a gathering" loading="lazy"></div>
            </div>
        </section>
        <section class="mx-auto grid max-w-7xl gap-12 px-5 py-16 lg:grid-cols-[0.8fr_1.2fr] lg:px-8 lg:py-24">
            <div><p class="eyebrow">The way we work</p><h2 class="mt-4 font-display text-4xl">Thoughtful from the first conversation.</h2></div>
            <div class="space-y-6 text-sm leading-8 text-ink/70">
                @if ($page?->body)
                    <p class="whitespace-pre-line">{{ $page->body }}</p>
                @else
                    <p>Company story, team details and service areas can be added in the Website CMS once they are confirmed.</p>
                    <p>We keep the process clear: learn what matters to you, prepare a proposal for review, and confirm the event only after the details are agreed.</p>
                @endif
                <a class="inline-flex items-center gap-2 font-semibold text-forest underline decoration-gold underline-offset-8" href="{{ route('contact') }}">Talk with our team →</a>
            </div>
        </section>
    </main>
@endsection
