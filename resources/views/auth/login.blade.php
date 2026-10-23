<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="theme-color" content="#0b3d2e"><link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Staff sign in · {{ config('app.name') }}</title>@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-screen place-items-center bg-cream px-5 py-12 text-ink">
    <main class="w-full max-w-md rounded-2xl border border-ink/10 bg-paper p-7 shadow-[0_24px_70px_-38px_rgba(13,48,36,0.5)] sm:p-10">
        <a class="inline-flex rounded-lg" href="{{ route('home') }}"><x-brand-mark mark-size="h-14 w-14" descriptor="Staff sign-in" /></a>
        <p class="eyebrow mt-10">Private workspace</p>
        <h1 class="mt-3 font-display text-4xl">Staff sign in</h1>
        <p class="mt-3 text-sm leading-6 text-ink/60">Use your staff account to manage inquiries and website content.</p>
        @if ($errors->any())<div role="alert" class="mt-6 rounded-sm border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif
        <form class="mt-7 grid gap-5" method="POST" action="{{ route('login.store') }}">
            @csrf
            <label class="grid gap-2 text-sm font-medium">Email address<input class="public-input" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus></label>
            <label class="grid gap-2 text-sm font-medium">Password<input class="public-input" type="password" name="password" autocomplete="current-password" required></label>
            <label class="flex items-center gap-2 text-xs text-ink/65"><input type="checkbox" name="remember" value="1" class="accent-forest">Keep me signed in</label>
            <button class="button-primary mt-2 w-full" type="submit">Sign in</button>
        </form>
        <a class="mt-7 inline-block text-xs font-medium text-forest underline" href="{{ route('home') }}">← Return to the website</a>
    </main>
</body>
</html>
