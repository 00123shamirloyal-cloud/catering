@extends('layouts.public')

@section('content')
    <main>
        <section class="relative isolate overflow-hidden bg-ink text-white">
            <img class="absolute inset-0 -z-20 h-full w-full object-cover opacity-45" src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=2200&q=85" alt="Warmly lit dinner table prepared for a gathering" fetchpriority="high">
            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-ink via-ink/85 to-ink/20"></div>
            <div class="mx-auto grid min-h-[610px] max-w-7xl items-center px-5 py-20 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-28">
                <div class="max-w-2xl">
                    <p class="mb-6 text-xs font-semibold uppercase tracking-[0.24em] text-[#dec394]">Catering · Weddings · Event planning · Rawalpindi</p>
                    <h1 class="font-display text-5xl leading-[1.08] sm:text-6xl lg:text-7xl">{{ $siteSettings['hero_title'] ?? 'Make room for a beautiful gathering.' }}</h1>
                    <p class="mt-7 max-w-xl text-base leading-8 text-white/75 sm:text-lg">{{ $siteSettings['hero_subtitle'] ?? 'Daata Catering and Event Organizer is based in Rawalpindi, Pakistan. Share your occasion, guest count and venue so we can discuss a suitable menu and event plan.' }}</p>
                    <div class="mt-9 flex flex-wrap gap-3">
                        <a href="{{ route('booking.create') }}" class="inline-flex items-center gap-3 bg-[#c0a06a] px-6 py-4 text-sm font-semibold text-ink hover:bg-[#d0b37f]">Plan your event <span aria-hidden="true">↗</span></a>
                        <a href="{{ route('services.index') }}" class="inline-flex items-center gap-3 border border-white/55 px-6 py-4 text-sm font-semibold text-white hover:bg-white/10">Explore our services</a>
                    </div>
                    <p class="mt-10 text-xs uppercase tracking-[0.15em] text-white/55">Based in Rawalpindi, Pakistan</p>
                </div>
                <div class="hidden lg:block">
                    <div class="ml-auto max-w-sm border border-white/25 bg-ink/45 p-7 backdrop-blur-sm">
                        <span class="font-display text-4xl text-[#d3b67e]">01</span>
                        <p class="mt-5 font-display text-2xl">A clear start to your event plan.</p>
                        <p class="mt-3 text-sm leading-7 text-white/70">Share your occasion, guest count and venue. The team can discuss menus, event setup and the next steps with you.</p>
                        <a class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-white hover:text-[#dec394]" href="{{ route('faqs') }}">How it works <span aria-hidden="true">→</span></a>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
            <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <div class="max-w-xl"><p class="eyebrow">Made for gathering</p><h2 class="mt-4 font-display text-4xl leading-tight sm:text-5xl">Good food, thoughtful hosting.</h2></div>
                <a href="{{ route('services.index') }}" class="text-sm font-semibold text-forest underline decoration-gold underline-offset-8">View all services <span aria-hidden="true">→</span></a>
            </div>
            <div class="mt-10 grid gap-5 md:grid-cols-3">
                @forelse ($services as $service)
                    <article class="group border border-ink/10 bg-white transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="aspect-[4/3] overflow-hidden bg-cream">
                            <img class="h-full w-full object-cover transition duration-500 group-hover:scale-105" src="{{ $service->image_path ? asset('storage/'.$service->image_path) : 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=900&q=80' }}" alt="{{ $service->metadata['alt_text'] ?? $service->title }}" loading="lazy">
                        </div>
                        <div class="p-6"><p class="eyebrow">{{ $service->category ?? 'Our services' }}</p><h3 class="mt-3 font-display text-2xl">{{ $service->title }}</h3><p class="mt-3 text-sm leading-6 text-ink/65">{{ $service->excerpt }}</p><a class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-forest" href="{{ route('services.show', $service->slug) }}">Discover this service <span aria-hidden="true">→</span></a></div>
                    </article>
                @empty
                    <div class="border border-dashed border-ink/20 p-8 text-sm text-ink/60 md:col-span-3">Our service collection is being prepared. <a class="font-semibold text-forest underline" href="{{ route('contact') }}">Talk with our team</a>.</div>
                @endforelse
            </div>
        </section>

        <section class="bg-cream">
            <div class="mx-auto grid max-w-7xl gap-12 px-5 py-20 lg:grid-cols-2 lg:items-center lg:px-8 lg:py-24">
                <div class="relative min-h-[360px] overflow-hidden bg-forest">
                    <img class="absolute inset-0 h-full w-full object-cover opacity-90" src="https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=1300&q=85" alt="A styled table setting with flowers and dinnerware" loading="lazy">
                    <div class="absolute bottom-5 left-5 border border-white/60 bg-ink/75 px-4 py-3 text-xs uppercase tracking-[0.18em] text-white">Gather beautifully</div>
                </div>
                <div class="max-w-xl">
                    <p class="eyebrow">Our process</p>
                    <h2 class="mt-4 font-display text-4xl leading-tight sm:text-5xl">From first idea to the final plate.</h2>
                    <ol class="mt-8 grid gap-6">
                        <li class="flex gap-5"><span class="font-display text-2xl text-clay">01</span><div><h3 class="font-semibold">Tell us what matters</h3><p class="mt-1 text-sm leading-6 text-ink/65">Share your date, guest count and the feeling you want your event to have.</p></div></li>
                        <li class="flex gap-5"><span class="font-display text-2xl text-clay">02</span><div><h3 class="font-semibold">Shape a proposal</h3><p class="mt-1 text-sm leading-6 text-ink/65">We will talk through menu options, service needs and a clear next step.</p></div></li>
                        <li class="flex gap-5"><span class="font-display text-2xl text-clay">03</span><div><h3 class="font-semibold">Enjoy being there</h3><p class="mt-1 text-sm leading-6 text-ink/65">When details are confirmed, the team prepares around your plan.</p></div></li>
                    </ol>
                    <a class="mt-8 inline-flex items-center gap-2 text-sm font-semibold text-forest underline decoration-gold underline-offset-8" href="{{ route('booking.create') }}">Start planning <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
            <div class="flex items-end justify-between gap-6"><div><p class="eyebrow">A place to begin</p><h2 class="mt-4 font-display text-4xl sm:text-5xl">Packages with room to make them yours.</h2></div><a class="hidden text-sm font-semibold text-forest underline decoration-gold underline-offset-8 sm:inline" href="{{ route('packages.index') }}">Explore packages →</a></div>
            <div class="mt-10 grid gap-5 md:grid-cols-2">
                @forelse ($packages as $package)
                    <article class="flex min-h-[280px] flex-col justify-between border border-ink/10 bg-white p-7 sm:p-9">
                        <div><p class="eyebrow">{{ $package->category ?? 'Catering package' }}</p><h3 class="mt-4 font-display text-3xl">{{ $package->title }}</h3><p class="mt-4 max-w-xl text-sm leading-7 text-ink/65">{{ $package->excerpt }}</p></div>
                        <div class="mt-7 flex items-center justify-between gap-4 border-t border-ink/10 pt-5"><span class="text-sm font-medium text-clay">{{ $package->metadata['price_label'] ?? 'Request a tailored quote' }}</span><a class="text-sm font-semibold text-forest" href="{{ route('packages.show', $package->slug) }}">Explore <span aria-hidden="true">→</span></a></div>
                    </article>
                @empty
                    <p class="border border-dashed border-ink/20 p-8 text-sm text-ink/60 md:col-span-2">Packages will appear here when they have been published in the website editor.</p>
                @endforelse
            </div>
        </section>

        <section class="bg-forest text-white">
            <div class="mx-auto grid max-w-7xl gap-10 px-5 py-20 lg:grid-cols-[0.8fr_1.2fr] lg:px-8 lg:py-24">
                <div><p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#d7bb86]">A little reassurance</p><h2 class="mt-4 font-display text-4xl sm:text-5xl">A considered approach to hosting.</h2><p class="mt-5 max-w-md text-sm leading-7 text-white/70">Every event is different. We begin with your priorities and share a clear proposal before a date is confirmed.</p><a href="{{ route('about') }}" class="mt-7 inline-flex items-center gap-2 text-sm font-semibold text-white underline decoration-[#d7bb86] underline-offset-8">Get to know our approach →</a></div>
                <div class="grid content-center gap-4 sm:grid-cols-2">
                    <div class="border border-white/20 p-6"><span class="font-display text-3xl text-[#d7bb86]">01</span><h3 class="mt-5 font-semibold">Personal planning</h3><p class="mt-2 text-sm leading-6 text-white/65">A real conversation about your event, guests and priorities.</p></div>
                    <div class="border border-white/20 p-6 sm:mt-12"><span class="font-display text-3xl text-[#d7bb86]">02</span><h3 class="mt-5 font-semibold">Clear proposals</h3><p class="mt-2 text-sm leading-6 text-white/65">Inclusions and pricing are confirmed for your own requirements.</p></div>
                    <div class="border border-white/20 p-6"><span class="font-display text-3xl text-[#d7bb86]">03</span><h3 class="mt-5 font-semibold">Thoughtful details</h3><p class="mt-2 text-sm leading-6 text-white/65">Menu, setup and timing are discussed before final confirmation.</p></div>
                    <div class="border border-white/20 p-6 sm:mt-12"><span class="font-display text-3xl text-[#d7bb86]">04</span><h3 class="mt-5 font-semibold">Clear next steps</h3><p class="mt-2 text-sm leading-6 text-white/65">The team confirms the proposal and event details with you before a booking is finalized.</p></div>
                </div>
            </div>
        </section>

        @if ($portfolio->isNotEmpty())
            <section class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
                <div class="flex items-end justify-between gap-6"><div><p class="eyebrow">Selected work</p><h2 class="mt-4 font-display text-4xl sm:text-5xl">Scenes from the table.</h2></div><a class="text-sm font-semibold text-forest underline decoration-gold underline-offset-8" href="{{ route('gallery.index') }}">View gallery →</a></div>
                <div class="mt-10 grid gap-5 md:grid-cols-3">
                    @foreach ($portfolio as $item)
                        <a href="{{ route('gallery.show', $item->slug) }}" class="group overflow-hidden bg-cream"><img class="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-105" src="{{ $item->image_path ? asset('storage/'.$item->image_path) : 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=900&q=80' }}" alt="{{ $item->metadata['alt_text'] ?? $item->title }}" loading="lazy"><div class="p-5"><p class="eyebrow">{{ $item->category ?? 'Event story' }}</p><h3 class="mt-2 font-display text-xl">{{ $item->title }}</h3></div></a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="bg-cream">
            <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-24">
                <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
                    <div><p class="eyebrow">Frequently asked</p><h2 class="mt-4 font-display text-4xl sm:text-5xl">A few useful details.</h2><a href="{{ route('faqs') }}" class="mt-7 inline-block text-sm font-semibold text-forest underline decoration-gold underline-offset-8">Read all FAQs →</a></div>
                    <div class="divide-y divide-ink/15 border-y border-ink/15">
                        @forelse ($faqs as $faq)
                            <details class="group py-5"><summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-medium"><span>{{ $faq->title }}</span><span class="text-xl text-clay transition group-open:rotate-45" aria-hidden="true">+</span></summary><p class="mt-3 max-w-2xl whitespace-pre-line text-sm leading-7 text-ink/65">{{ $faq->body }}</p></details>
                        @empty
                            <p class="py-6 text-sm text-ink/60">Questions and answers will appear here when approved in the website editor.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-24">
            <div class="flex flex-col justify-between gap-7 border-b border-ink/15 pb-10 sm:flex-row sm:items-end">
                <div class="max-w-2xl"><p class="eyebrow">Your occasion, your way</p><h2 class="mt-4 font-display text-4xl sm:text-5xl">Let’s make a plan that feels like you.</h2><p class="mt-4 text-sm leading-7 text-ink/65">Share the basics and our team will get back to you to discuss availability and next steps.</p></div>
                <a href="{{ route('booking.create') }}" class="button-primary shrink-0">Request a conversation <span aria-hidden="true">↗</span></a>
            </div>
            @if ($testimonials->isNotEmpty())
                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    @foreach ($testimonials as $testimonial)
                        <blockquote class="border-l-2 border-gold pl-5"><p class="font-display text-xl leading-8">“{{ $testimonial->body }}”</p><footer class="mt-4 text-xs font-semibold uppercase tracking-[0.16em] text-ink/55">{{ $testimonial->metadata['attribution'] ?? 'Guest' }}</footer></blockquote>
                    @endforeach
                </div>
            @endif
        </section>
    </main>
@endsection
