@props([
    'inverse' => false,
    'compact' => false,
    'markSize' => 'h-12 w-12',
    'descriptor' => 'Catering · Event Organizer',
])

@php($brandName = data_get($siteSettings ?? [], 'business_name') ?: config('app.name'))

<span {{ $attributes->class(['inline-flex min-w-0 items-center gap-3']) }}>
    <img class="{{ $markSize }} shrink-0" src="{{ asset('images/daata-logo-placeholder.svg') }}" alt="">
    @unless ($compact)
        <span class="min-w-0">
            <span class="block max-w-full font-display text-lg leading-tight {{ $inverse ? 'text-[#fbf9f3]' : 'text-ink' }}">{{ $brandName }}</span>
            <span class="mt-1 block text-[9px] font-semibold uppercase tracking-[0.18em] {{ $inverse ? 'text-[#d7c18f]' : 'text-clay' }}">{{ $descriptor }}</span>
        </span>
    @endunless
</span>
