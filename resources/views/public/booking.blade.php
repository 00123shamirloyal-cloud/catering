@extends('layouts.public')

@section('content')
    <main>
        <section class="bg-cream"><div class="mx-auto max-w-7xl px-5 py-14 lg:px-8 lg:py-20"><p class="eyebrow">Plan your event</p><h1 class="mt-4 max-w-3xl font-display text-5xl leading-tight sm:text-6xl">Start with the details you know.</h1><p class="mt-5 max-w-2xl text-base leading-8 text-ink/65">This is an inquiry, not a confirmed booking. Our team will review your request and contact you about availability and next steps.</p></div></section>
        <section class="mx-auto max-w-4xl px-5 py-12 lg:py-16">
            @if ($errors->any())
                <div role="alert" class="mb-6 rounded-sm border border-red-200 bg-red-50 p-5 text-sm text-red-800"><p class="font-semibold">Please review your details.</p><ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form data-stepper method="POST" action="{{ route('booking.store') }}" class="border border-ink/10 bg-white p-6 sm:p-9">
                @csrf
                <input type="hidden" name="idempotency_token" value="{{ old('idempotency_token', $idempotencyToken) }}">
                <div class="absolute -left-[10000px] top-auto h-px w-px overflow-hidden" aria-hidden="true"><label>Leave blank<input name="website" tabindex="-1" autocomplete="off"></label></div>
                <div class="mb-8 flex items-center justify-between border-b border-ink/10 pb-5"><div><p class="eyebrow">Event inquiry</p><p data-step-progress class="mt-2 text-sm font-medium">Complete all sections</p></div><span class="font-display text-3xl text-gold">✳</span></div>
                <section data-step-panel aria-labelledby="step-event-title">
                    <h2 id="step-event-title" class="font-display text-3xl">01 · Event information</h2>
                    <div class="mt-7 grid gap-5 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm font-medium sm:col-span-2">Event type<input class="public-input" name="event_type" list="event-types" value="{{ old('event_type') }}" placeholder="Mehndi, Baraat, Walima, corporate event…" required>@error('event_type')<span class="text-xs text-red-700">{{ $message }}</span>@enderror</label>
                        <datalist id="event-types"><option>Mehndi</option><option>Baraat</option><option>Walima</option><option>Wedding</option><option>Family celebration</option><option>Corporate event</option><option>Birthday celebration</option><option>Private party</option><option>Other</option></datalist>
                        <label class="grid gap-2 text-sm font-medium">Preferred date<input class="public-input" type="date" name="event_date" min="{{ now()->toDateString() }}" value="{{ old('event_date') }}" required>@error('event_date')<span class="text-xs text-red-700">{{ $message }}</span>@enderror</label>
                        <label class="grid gap-2 text-sm font-medium">Alternative date <span class="font-normal text-ink/45">Optional</span><input class="public-input" type="date" name="alternative_date" min="{{ now()->toDateString() }}" value="{{ old('alternative_date') }}"></label>
                        <label class="grid gap-2 text-sm font-medium">Start time <span class="font-normal text-ink/45">Optional</span><input class="public-input" type="time" name="start_time" value="{{ old('start_time') }}"></label>
                        <label class="grid gap-2 text-sm font-medium">End time <span class="font-normal text-ink/45">Optional</span><input class="public-input" type="time" name="end_time" value="{{ old('end_time') }}"></label>
                        <label class="grid gap-2 text-sm font-medium">Estimated guests<input class="public-input" type="number" name="guest_count" min="1" max="50000" value="{{ old('guest_count') }}" required>@error('guest_count')<span class="text-xs text-red-700">{{ $message }}</span>@enderror</label>
                        <label class="grid gap-2 text-sm font-medium">City<input class="public-input" name="city" value="{{ old('city', $siteSettings['city'] ?? 'Rawalpindi') }}" placeholder="e.g. Rawalpindi" required>@error('city')<span class="text-xs text-red-700">{{ $message }}</span>@enderror</label>
                        <label class="grid gap-2 text-sm font-medium sm:col-span-2">Venue or address <span class="font-normal text-ink/45">Optional</span><input class="public-input" name="venue" value="{{ old('venue') }}"></label>
                        <fieldset class="grid gap-2 text-sm sm:col-span-2"><legend class="font-medium">Setting <span class="font-normal text-ink/45">Optional</span></legend><div class="flex flex-wrap gap-4 pt-1">@foreach (['indoor' => 'Indoor', 'outdoor' => 'Outdoor', 'unsure' => 'Not sure'] as $value => $label)<label class="flex items-center gap-2"><input type="radio" name="setting" value="{{ $value }}" class="accent-forest" @checked(old('setting') === $value)>{{ $label }}</label>@endforeach</div></fieldset>
                    </div>
                    <div class="mt-8 flex justify-end"><button type="button" data-step-next class="button-primary">Continue <span aria-hidden="true">→</span></button></div>
                </section>

                <section data-step-panel aria-labelledby="step-service-title">
                    <h2 id="step-service-title" class="font-display text-3xl">02 · Services & menus</h2>
                    <p class="mt-3 text-sm leading-6 text-ink/60">Choose any starting points. Your selections are for discussion and can be adjusted.</p>
                    @if ($services->isNotEmpty())
                        <fieldset class="mt-7"><legend class="text-sm font-semibold">Services</legend><div class="mt-3 grid gap-3 sm:grid-cols-2">@foreach ($services as $service)<label class="flex items-start gap-3 border border-ink/10 p-4"><input class="mt-1 accent-forest" type="checkbox" name="service_ids[]" value="{{ $service->id }}" @checked(in_array($service->id, old('service_ids', $selectedServiceId ? [$selectedServiceId] : [])))><span><span class="block text-sm font-medium">{{ $service->title }}</span><span class="mt-1 block text-xs leading-5 text-ink/55">{{ $service->excerpt }}</span></span></label>@endforeach</div></fieldset>
                    @endif
                    <label class="mt-7 grid gap-2 text-sm font-medium">Package <span class="font-normal text-ink/45">Optional</span><select class="public-input" name="package_id"><option value="">I’d like a custom proposal</option>@foreach ($packages as $package)<option value="{{ $package->id }}" @selected((string) old('package_id', $selectedPackageId) === (string) $package->id)>{{ $package->title }} · {{ $package->metadata['price_label'] ?? 'Request a quote' }}</option>@endforeach</select></label>
                    <div class="mt-8 flex justify-between"><button type="button" data-step-prev class="border border-ink/20 px-5 py-3 text-sm font-semibold">Back</button><button type="button" data-step-next class="button-primary">Continue <span aria-hidden="true">→</span></button></div>
                </section>

                <section data-step-panel aria-labelledby="step-requirements-title">
                    <h2 id="step-requirements-title" class="font-display text-3xl">03 · Budget & requirements</h2>
                    <div class="mt-7 grid gap-5">
                        <label class="grid gap-2 text-sm font-medium">Budget preference <span class="font-normal text-ink/45">Optional</span><select class="public-input" name="budget_range"><option value="">Prefer to discuss</option>@foreach (['Flexible', 'Working within a set budget', 'Not sure yet'] as $range)<option @selected(old('budget_range') === $range)>{{ $range }}</option>@endforeach</select><span class="text-xs font-normal text-ink/50">Share a preference without entering an unconfirmed price range.</span></label>
                        <label class="grid gap-2 text-sm font-medium">Dietary preferences or allergen questions <span class="font-normal text-ink/45">Optional</span><textarea class="public-input min-h-24" name="dietary_preferences">{{ old('dietary_preferences') }}</textarea></label>
                        <label class="grid gap-2 text-sm font-medium">Accessibility, setup or other requirements <span class="font-normal text-ink/45">Optional</span><textarea class="public-input min-h-24" name="requirements">{{ old('requirements') }}</textarea></label>
                    </div>
                    <div class="mt-8 flex justify-between"><button type="button" data-step-prev class="border border-ink/20 px-5 py-3 text-sm font-semibold">Back</button><button type="button" data-step-next class="button-primary">Continue <span aria-hidden="true">→</span></button></div>
                </section>

                <section data-step-panel aria-labelledby="step-contact-title">
                    <h2 id="step-contact-title" class="font-display text-3xl">04 · Your contact details</h2>
                    <div class="mt-7 grid gap-5 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm font-medium sm:col-span-2">Full name<input class="public-input" name="full_name" value="{{ old('full_name') }}" autocomplete="name" required>@error('full_name')<span class="text-xs text-red-700">{{ $message }}</span>@enderror</label>
                        <label class="grid gap-2 text-sm font-medium">Email address<input class="public-input" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>@error('email')<span class="text-xs text-red-700">{{ $message }}</span>@enderror</label>
                        <label class="grid gap-2 text-sm font-medium">Phone number<input class="public-input" type="tel" inputmode="tel" name="phone" value="{{ old('phone') }}" placeholder="+92 3xx xxx xxxx" autocomplete="tel" required>@error('phone')<span class="text-xs text-red-700">{{ $message }}</span>@enderror</label>
                        <label class="grid gap-2 text-sm font-medium">Preferred contact method<select class="public-input" name="preferred_contact" required><option value="phone" @selected(old('preferred_contact') === 'phone')>Phone</option><option value="email" @selected(old('preferred_contact') === 'email')>Email</option><option value="whatsapp" @selected(old('preferred_contact') === 'whatsapp')>WhatsApp</option></select></label>
                        <label class="grid gap-2 text-sm font-medium sm:col-span-2">Additional message <span class="font-normal text-ink/45">Optional</span><textarea class="public-input min-h-24" name="message">{{ old('message') }}</textarea></label>
                        <label class="flex items-start gap-3 text-xs leading-5 text-ink/65 sm:col-span-2"><input type="checkbox" name="privacy_accepted" value="1" class="mt-1 accent-forest" required @checked(old('privacy_accepted'))><span>I have read the <a class="underline" href="{{ route('policies.show', 'privacy-policy') }}">privacy notice</a> and agree that the team can contact me about this event inquiry.</span></label>
                        @error('privacy_accepted')<span class="text-xs text-red-700 sm:col-span-2">{{ $message }}</span>@enderror
                    </div>
                    <div class="mt-8 flex flex-wrap justify-between gap-3"><button type="button" data-step-prev class="border border-ink/20 px-5 py-3 text-sm font-semibold">Back</button><button type="submit" data-step-submit class="button-primary">Send inquiry <span aria-hidden="true">↗</span></button></div>
                </section>
            </form>
            <p class="mx-auto mt-5 max-w-3xl text-center text-xs leading-5 text-ink/50">Your details are used to respond to this request. Sending an inquiry does not reserve a date or confirm an event.</p>
        </section>
    </main>
@endsection
