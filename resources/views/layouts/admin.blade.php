<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="theme-color" content="#0b3d2e"><link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ $pageTitle ?? 'Dashboard' }} · {{ config('app.name') }}</title>@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper text-ink dark:bg-slate-950 dark:text-slate-100">
    <div class="min-h-screen">
        <aside data-admin-sidebar class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-white/10 bg-[#0c3128] text-white transition-transform duration-200 lg:translate-x-0">
            <div class="flex min-h-24 items-center border-b border-white/10 px-5 py-4">
                <x-brand-mark inverse mark-size="h-11 w-11" descriptor="Operations workspace" />
            </div>
            <nav class="flex-1 space-y-1 overflow-y-auto p-4 text-sm" aria-label="CRM navigation">
                <p class="mb-3 px-3 pt-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-white/40">Workspace</p>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 font-semibold text-[#e4ce99]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" href="{{ route('admin.dashboard') }}"><span>◫</span> Dashboard</a>
                @can('view-crm')
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.inquiries.*') ? 'bg-white/10 font-semibold text-[#e4ce99]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" href="{{ route('admin.inquiries.index') }}"><span>✉</span> Inquiries</a>
                    @can('manage-customers')<a class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.customers.*') ? 'bg-white/10 font-semibold text-[#e4ce99]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" href="{{ route('admin.customers.index') }}"><span>♙</span> Customers</a>@endcan
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.tasks.*') ? 'bg-white/10 font-semibold text-[#e4ce99]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" href="{{ route('admin.tasks.index') }}"><span>✓</span> Follow-up tasks</a>
                    <a class="flex items-center justify-between rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.contacts.*') ? 'bg-white/10 font-semibold text-[#e4ce99]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" href="{{ route('admin.contacts.index') }}"><span class="flex items-center gap-3"><span>☏</span> Contact messages</span></a>
                @endcan
                @can('manage-appointments')<a class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.calendar') || request()->routeIs('admin.appointments.*') ? 'bg-white/10 font-semibold text-[#e4ce99]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" href="{{ route('admin.calendar') }}"><span>▦</span> Calendar</a>@endcan
                @can('manage-events')<a class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.events.*') ? 'bg-white/10 font-semibold text-[#e4ce99]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" href="{{ route('admin.events.index') }}"><span>✳</span> Events</a>@endcan
                @can('manage-finance')
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.quotations.*') ? 'bg-white/10 font-semibold text-[#e4ce99]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" href="{{ route('admin.quotations.index') }}"><span>▧</span> Quotations</a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.invoices.*') ? 'bg-white/10 font-semibold text-[#e4ce99]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" href="{{ route('admin.invoices.index') }}"><span>▤</span> Invoices</a>
                @endcan
                @can('manage-staff')<a class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.staff.*') ? 'bg-white/10 font-semibold text-[#e4ce99]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" href="{{ route('admin.staff.index') }}"><span>♙</span> Staff</a>@endcan
                @can('view-reports')<a class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.reports.*') ? 'bg-white/10 font-semibold text-[#e4ce99]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" href="{{ route('admin.reports.index') }}"><span>▥</span> Reports</a>@endcan
                <p class="mb-3 mt-8 px-3 text-[10px] font-semibold uppercase tracking-[0.18em] text-white/40">Website</p>
                @can('manage-website')
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.content.*') ? 'bg-white/10 font-semibold text-[#e4ce99]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" href="{{ route('admin.content.index') }}"><span>▤</span> Website content</a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.settings.*') ? 'bg-white/10 font-semibold text-[#e4ce99]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" href="{{ route('admin.settings.edit') }}"><span>⚙</span> Website settings</a>
                @endcan
            </nav>
            <div class="border-t border-white/10 p-4">
                <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-xs text-white/65 hover:bg-white/10 hover:text-white">↗ View public website</a>
                <div class="mt-3 flex items-center justify-between rounded-lg bg-white/5 p-3"><div class="min-w-0"><p class="truncate text-xs font-semibold text-white">{{ auth()->user()->name }}</p><p class="truncate text-[10px] capitalize text-white/50">{{ str_replace('_', ' ', auth()->user()->role) }}</p></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="text-xs text-white/60 hover:text-[#e4ce99]" type="submit" aria-label="Sign out">↗</button></form></div>
            </div>
        </aside>

        <div class="min-h-screen lg:pl-72">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-ink/10 bg-paper/95 px-4 backdrop-blur sm:px-7 dark:border-slate-800 dark:bg-slate-900/95">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" data-sidebar-toggle aria-expanded="false" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-slate-600 lg:hidden dark:border-slate-700 dark:text-slate-300" aria-label="Toggle sidebar">☰</button>
                    <div class="hidden min-w-0 sm:block"><p class="text-[10px] uppercase tracking-[0.16em] text-clay">Daata · Operations</p><p class="truncate text-sm font-semibold">{{ $pageTitle ?? 'Dashboard' }}</p></div>
                </div>
                <div class="flex items-center gap-2 sm:gap-4">
                    @can('view-crm')<form class="hidden sm:block" method="GET" action="{{ route('admin.search') }}"><label class="sr-only" for="global-search">Search the workspace</label><input id="global-search" class="w-56 rounded-lg border border-[#ded8ca] bg-white px-3 py-2 text-xs outline-none focus:border-gold dark:border-slate-700 dark:bg-slate-800" name="q" placeholder="Search customers, inquiries…" value="{{ request('q') }}"></form>@endcan
                    <button type="button" data-theme-toggle class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-slate-600 dark:border-slate-700 dark:text-slate-300" aria-label="Toggle dark theme">◐</button>
                    <span class="hidden text-xs text-slate-500 md:inline dark:text-slate-400">{{ now()->format('D, M j') }}</span>
                </div>
            </header>
            <main class="mx-auto max-w-7xl px-4 py-7 sm:px-7 sm:py-9">
                @if (session('success'))<div role="status" class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200">{{ session('success') }}</div>@endif
                @if (session('share_link'))<div role="status" class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100"><p class="font-semibold">No email was sent because the mailer is set to log.</p><p class="mt-1">Copy this private proposal link into your approved customer channel:</p><a class="mt-2 block break-all font-medium underline" href="{{ session('share_link') }}">{{ session('share_link') }}</a></div>@endif
                @if ($errors->any())<div role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/50 dark:text-red-200">{{ $errors->first() }}</div>@endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
