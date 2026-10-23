@extends('layouts.public')

@section('content')
    <main class="mx-auto max-w-4xl px-5 py-16 lg:py-24">
        @if ($page)
            <p class="eyebrow">{{ $page->category ?? 'Company information' }}</p>
            <h1 class="mt-4 font-display text-5xl">{{ $page->title }}</h1>
            @if ($page->excerpt)<p class="mt-5 text-lg leading-8 text-ink/65">{{ $page->excerpt }}</p>@endif
            <div class="mt-10 whitespace-pre-line border-t border-ink/15 pt-8 text-sm leading-8 text-ink/70">{{ $page->body }}</div>
        @else
            <p class="eyebrow">Company information</p>
            <h1 class="mt-4 font-display text-5xl">{{ Str::headline(str_replace('-', ' ', request()->route('slug'))) }}</h1>
            <p class="mt-6 border-l-2 border-gold pl-5 text-base leading-8 text-ink/70">This policy page is being prepared for review. The owner must add and approve company-specific legal terms before publishing it.</p>
        @endif
    </main>
@endsection
