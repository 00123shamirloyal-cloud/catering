@extends('layouts.admin')

@section('content')
    @php
        $previousDate = match ($viewMode) {'day' => $date->subDay(), 'week' => $date->subWeek(), 'agenda' => $date->subDays(30), default => $date->subMonth()};
        $nextDate = match ($viewMode) {'day' => $date->addDay(), 'week' => $date->addWeek(), 'agenda' => $date->addDays(30), default => $date->addMonth()};
    @endphp
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-forest dark:text-emerald-300">Appointments & events</p><h1 class="mt-2 font-display text-4xl">Calendar</h1><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">All times are shown in {{ config('app.timezone') }}.</p></div><a class="rounded-lg bg-forest px-4 py-2.5 text-center text-sm font-semibold text-white" href="{{ route('admin.appointments.create') }}">Schedule appointment +</a></div>
    <section class="admin-card mt-7">
        <div class="flex flex-col gap-4 border-b border-slate-200 pb-5 xl:flex-row xl:items-center xl:justify-between dark:border-slate-800">
            <div class="flex items-center gap-2"><a class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-lg dark:border-slate-700" href="{{ route('admin.calendar', ['date' => $previousDate->toDateString(), 'view' => $viewMode]) }}" aria-label="Previous period">‹</a><h2 class="min-w-44 text-center font-display text-2xl">{{ $startsAt->format('M j') }} – {{ $endsAt->format('M j, Y') }}</h2><a class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-lg dark:border-slate-700" href="{{ route('admin.calendar', ['date' => $nextDate->toDateString(), 'view' => $viewMode]) }}" aria-label="Next period">›</a><a class="ml-2 text-xs font-semibold text-forest underline dark:text-emerald-300" href="{{ route('admin.calendar', ['date' => now()->toDateString(), 'view' => $viewMode]) }}">Today</a></div>
            <div class="flex flex-wrap gap-1 rounded-lg bg-slate-100 p-1 dark:bg-slate-800">@foreach (['day' => 'Day', 'week' => 'Week', 'month' => 'Month', 'agenda' => 'Agenda'] as $key => $label)<a class="rounded-md px-3 py-1.5 text-xs font-medium {{ $viewMode === $key ? 'bg-white text-forest shadow-sm dark:bg-slate-700 dark:text-emerald-200' : 'text-slate-500 dark:text-slate-400' }}" href="{{ route('admin.calendar', ['date' => $date->toDateString(), 'view' => $key]) }}">{{ $label }}</a>@endforeach</div>
        </div>
        @if ($viewMode === 'month')
            <div class="mt-5 grid grid-cols-7 border-l border-t border-slate-200 dark:border-slate-800">@foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayName)<div class="border-b border-r border-slate-200 bg-slate-50 px-2 py-2 text-center text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:border-slate-800 dark:bg-slate-800/40">{{ $dayName }}</div>@endforeach
                @foreach ($days as $day)
                    @php $items = $entriesByDate->get($day->toDateString(), collect()); @endphp
                    <div class="min-h-28 border-b border-r border-slate-200 p-1.5 sm:min-h-36 sm:p-2 dark:border-slate-800 {{ $day->month !== $date->month ? 'bg-slate-50/60 dark:bg-slate-900/60' : '' }}">
                        <span class="grid h-6 w-6 place-items-center rounded-full text-xs {{ $day->isToday() ? 'bg-forest font-semibold text-white' : 'text-slate-500 dark:text-slate-400' }}">{{ $day->day }}</span>
                        <div class="mt-1 grid gap-1">
                            @foreach ($items->take(3) as $item)
                                @if ($item['url'])<a class="truncate rounded px-1.5 py-1 text-[9px] sm:text-[10px] {{ $item['color'] === 'forest' ? 'bg-mist text-forest dark:bg-forest/30 dark:text-emerald-200' : 'bg-amber-50 text-amber-900 dark:bg-amber-950/60 dark:text-amber-200' }}" href="{{ $item['url'] }}" title="{{ $item['title'] }} · {{ $item['time'] }}">{{ $item['time'] }} {{ $item['title'] }}</a>@else<span class="truncate rounded px-1.5 py-1 text-[9px] sm:text-[10px] bg-amber-50 text-amber-900 dark:bg-amber-950/60 dark:text-amber-200" title="{{ $item['title'] }}">{{ $item['time'] }} {{ $item['title'] }}</span>@endif
                            @endforeach
                            @if ($items->count() > 3)<a class="px-1 text-[10px] text-slate-500" href="{{ route('admin.calendar', ['date' => $day->toDateString(), 'view' => 'day']) }}">+{{ $items->count() - 3 }} more</a>@endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="mt-5 space-y-7">
                @forelse ($entriesByDate as $day => $items)
                    <section><h3 class="border-b border-slate-100 pb-2 text-xs font-semibold uppercase tracking-[0.14em] text-slate-500 dark:border-slate-800 dark:text-slate-400">{{ \Carbon\CarbonImmutable::parse($day)->format('l, F j') }}</h3><div class="mt-3 grid gap-3">@foreach ($items as $item)<article class="flex flex-col justify-between gap-3 rounded-lg border border-slate-100 p-4 sm:flex-row sm:items-center dark:border-slate-800"><div class="flex gap-3"><span class="mt-1 h-9 w-1 rounded-full {{ $item['color'] === 'forest' ? 'bg-forest' : 'bg-gold' }}"></span><div><p class="text-sm font-semibold">{{ $item['title'] }}</p><p class="mt-1 text-xs capitalize text-slate-500 dark:text-slate-400">{{ $item['time'] }} · {{ $item['kind'] }} · {{ $item['person'] }}</p></div></div><div class="flex items-center gap-3 text-xs"><span class="text-slate-400">{{ $item['assigned'] }}</span>@if ($item['url'])<a class="font-semibold text-forest underline dark:text-emerald-300" href="{{ $item['url'] }}">Open →</a>@endif</div></article>@endforeach</div></section>
                @empty
                    <div class="py-14 text-center"><h3 class="font-display text-2xl">No appointments or events in this period</h3><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Schedule a consultation or create a tentative event hold from an inquiry.</p><a class="mt-5 inline-block text-xs font-semibold text-forest underline dark:text-emerald-300" href="{{ route('admin.appointments.create') }}">Schedule an appointment →</a></div>
                @endforelse
            </div>
        @endif
        <div class="mt-5 flex flex-wrap gap-4 border-t border-slate-200 pt-4 text-[11px] text-slate-500 dark:border-slate-800 dark:text-slate-400"><span class="flex items-center gap-2"><i class="h-2.5 w-2.5 rounded-full bg-forest"></i> Events</span><span class="flex items-center gap-2"><i class="h-2.5 w-2.5 rounded-full bg-gold"></i> Appointments</span><span>Expired tentative holds are excluded from the calendar.</span></div>
    </section>
@endsection
