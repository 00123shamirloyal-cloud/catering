@extends('layouts.public')

@section('content')
    <main class="mx-auto max-w-3xl px-5 py-20 text-center lg:px-8 lg:py-28"><p class="eyebrow">Proposal accepted</p><h1 class="mt-4 font-display text-4xl sm:text-5xl">Thank you, {{ $quotation->customer->name }}</h1><p class="mx-auto mt-5 max-w-xl text-sm leading-7 text-ink/65">Your approval of revision {{ $quotation->revision_number }} has been recorded. This does not itself confirm the event booking; our team will follow up with the next steps.</p><a class="button-primary mt-8" href="{{ route('home') }}">Return to the website</a></main>
@endsection
