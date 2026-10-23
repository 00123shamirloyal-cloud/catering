@extends('layouts.public')

@section('content')
    <main>
        <section class="bg-cream"><div class="mx-auto max-w-7xl px-5 py-16 lg:px-8 lg:py-24"><p class="eyebrow">Helpful details</p><h1 class="mt-4 font-display text-5xl sm:text-6xl">Frequently asked questions</h1><p class="mt-5 max-w-2xl text-base leading-8 text-ink/65">Answers to common planning questions. Ask us if you need something more specific to your event.</p></div></section>
        <section class="mx-auto max-w-4xl px-5 py-14 lg:py-20">
            <div class="divide-y divide-ink/15 border-y border-ink/15">
                @forelse ($faqs as $faq)
                    <details class="group py-6"><summary class="flex cursor-pointer list-none items-center justify-between gap-5 font-display text-xl"><span>{{ $faq->title }}</span><span class="text-2xl text-clay transition group-open:rotate-45" aria-hidden="true">+</span></summary><p class="mt-4 whitespace-pre-line text-sm leading-7 text-ink/65">{{ $faq->body }}</p></details>
                @empty
                    <p class="py-8 text-sm text-ink/60">FAQs will appear here after a team member publishes them in the website editor.</p>
                @endforelse
            </div>
        </section>
    </main>
@endsection
