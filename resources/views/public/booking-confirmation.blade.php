@extends('layouts.public')

@section('content')
    <main class="mx-auto max-w-3xl px-5 py-20 text-center lg:py-28">
        <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-mist text-2xl text-forest" aria-hidden="true">✓</span>
        <p class="eyebrow mt-7">Inquiry received</p>
        <h1 class="mt-4 font-display text-5xl leading-tight">Thank you, {{ $inquiry->customer_name }}.</h1>
        <p class="mx-auto mt-5 max-w-xl text-base leading-8 text-ink/65">Your request is with the team. They will review your event details and get in touch using your preferred contact method.</p>
        <div class="mx-auto mt-9 max-w-sm border border-ink/10 bg-cream p-6"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-ink/55">Your inquiry reference</p><p class="mt-2 font-display text-2xl">{{ $inquiry->reference }}</p></div>
        <p class="mx-auto mt-7 max-w-xl text-sm leading-6 text-ink/55">This is not a confirmed booking and your date has not been reserved. A team member will confirm availability and next steps with you.</p>
        <a class="button-primary mt-8" href="{{ route('home') }}">Return to the homepage</a>
    </main>
@endsection
