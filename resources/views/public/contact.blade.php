@extends('layouts.public')

@section('content')
    <main>
        <section class="bg-cream"><div class="mx-auto max-w-7xl px-5 py-16 lg:px-8 lg:py-24"><p class="eyebrow">Daata · Rawalpindi, Pakistan</p><h1 class="mt-4 max-w-3xl font-display text-5xl leading-tight sm:text-6xl">Tell us what you’re planning.</h1><p class="mt-5 max-w-2xl text-base leading-8 text-ink/65">Send an event enquiry or a note for the team. A message is not a booking confirmation.</p></div></section>
        <section class="mx-auto grid max-w-7xl gap-12 px-5 py-14 lg:grid-cols-[0.7fr_1.3fr] lg:px-8 lg:py-20">
            <div class="space-y-8">
                @if (! empty($siteSettings['phone']))<div><p class="eyebrow">Phone</p><p class="mt-2 text-sm"><a class="font-medium text-forest underline" href="tel:{{ $siteSettings['phone'] }}">{{ $siteSettings['phone'] }}</a></p></div>@endif
                @if (! empty($siteSettings['email']))<div><p class="eyebrow">Email</p><p class="mt-2 text-sm"><a class="font-medium text-forest underline" href="mailto:{{ $siteSettings['email'] }}">{{ $siteSettings['email'] }}</a></p></div>@endif
                @if (! empty($siteSettings['address']) || ! empty($siteSettings['city']))<div><p class="eyebrow">Location</p>@if (! empty($siteSettings['address']))<p class="mt-2 text-sm leading-6">{{ $siteSettings['address'] }}</p>@endif @if (! empty($siteSettings['city']))<a class="mt-2 inline-block text-sm font-medium text-forest underline decoration-gold underline-offset-4" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode(trim($siteSettings['city']).', Pakistan') }}" target="_blank" rel="noopener">{{ $siteSettings['city'] }}, Pakistan · View city map ↗</a>@endif</div>@endif
                <div><p class="eyebrow">Hours</p><p class="mt-2 whitespace-pre-line text-sm leading-6">{{ $siteSettings['business_hours'] ?? 'By consultation' }}</p></div>
                @if (! empty($siteSettings['service_areas']))<div><p class="eyebrow">Service areas</p><p class="mt-2 text-sm leading-6">{{ implode(', ', (array) $siteSettings['service_areas']) }}</p></div>@endif
                @if (! empty($siteSettings['whatsapp_url']))<a class="inline-flex items-center gap-2 text-sm font-semibold text-forest underline decoration-gold underline-offset-8" href="{{ $siteSettings['whatsapp_url'] }}" target="_blank" rel="noopener">Message us on WhatsApp ↗</a>@endif
            </div>
            <form method="POST" action="{{ route('contact.store') }}" class="border border-ink/10 bg-white p-6 sm:p-9">
                @csrf
                <h2 class="font-display text-3xl">Send a message</h2>
                @if ($errors->any())<div role="alert" class="mt-5 rounded-sm bg-red-50 p-4 text-sm text-red-800">Please check the form and correct the fields marked below.</div>@endif
                <div class="mt-7 grid gap-5 sm:grid-cols-2">
                    <label class="grid gap-2 text-sm font-medium">Your name<input class="public-input" name="name" value="{{ old('name') }}" required autocomplete="name">@error('name')<span class="text-xs text-red-700">{{ $message }}</span>@enderror</label>
                    <label class="grid gap-2 text-sm font-medium">Email address<input class="public-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">@error('email')<span class="text-xs text-red-700">{{ $message }}</span>@enderror</label>
                    <label class="grid gap-2 text-sm font-medium">Phone <span class="font-normal text-ink/45">Optional</span><input class="public-input" type="tel" inputmode="tel" name="phone" value="{{ old('phone') }}" placeholder="+92 3xx xxx xxxx" autocomplete="tel"></label>
                    <label class="grid gap-2 text-sm font-medium">Subject <span class="font-normal text-ink/45">Optional</span><input class="public-input" name="subject" value="{{ old('subject') }}"></label>
                    <label class="grid gap-2 text-sm font-medium sm:col-span-2">Message<textarea class="public-input min-h-36" name="message" required>{{ old('message') }}</textarea>@error('message')<span class="text-xs text-red-700">{{ $message }}</span>@enderror</label>
                    <div class="absolute -left-[10000px] top-auto h-px w-px overflow-hidden" aria-hidden="true"><label>Leave blank<input name="website" tabindex="-1" autocomplete="off"></label></div>
                    <label class="flex items-start gap-3 text-xs leading-5 text-ink/65 sm:col-span-2"><input type="checkbox" name="privacy_accepted" value="1" class="mt-1 accent-forest" required @checked(old('privacy_accepted'))><span>I have read the <a class="underline" href="{{ route('policies.show', 'privacy-policy') }}">privacy notice</a> and agree to be contacted about this message.</span></label>
                    @error('privacy_accepted')<span class="text-xs text-red-700 sm:col-span-2">{{ $message }}</span>@enderror
                    <button class="button-primary sm:col-span-2 sm:justify-self-start" type="submit">Send message <span aria-hidden="true">↗</span></button>
                </div>
            </form>
        </section>
    </main>
@endsection
