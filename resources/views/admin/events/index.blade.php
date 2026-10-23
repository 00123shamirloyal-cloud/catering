@extends('layouts.admin')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-forest dark:text-emerald-300">Event operations</p><h1 class="mt-2 font-display text-4xl">Events</h1><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Tentative holds and confirmed work, linked to each original inquiry.</p></div>
        <a class="rounded-lg border border-slate-200 px-4 py-2.5 text-center text-sm font-semibold dark:border-slate-700" href="{{ route('admin.calendar') }}">Open calendar</a>
    </div>
    <section class="admin-card mt-7 overflow-hidden p-0">
        <div class="overflow-x-auto"><table class="w-full min-w-[760px] text-left text-sm"><thead class="bg-slate-50 text-[10px] uppercase tracking-[0.14em] text-slate-400 dark:bg-slate-800/60"><tr><th class="px-5 py-3">Event</th><th class="px-5 py-3">Customer</th><th class="px-5 py-3">Date & venue</th><th class="px-5 py-3">Manager</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr></thead><tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($events as $event)
                <tr><td class="px-5 py-4"><p class="font-semibold">{{ $event->title }}</p><p class="mt-1 text-xs text-slate-400">{{ $event->reference }} · {{ $event->category }}</p></td><td class="px-5 py-4"><p>{{ $event->customer->name }}</p><p class="mt-1 text-xs text-slate-400">{{ $event->customer->email }}</p></td><td class="px-5 py-4"><p>{{ $event->event_date->format('M j, Y') }}</p><p class="mt-1 text-xs text-slate-400">{{ $event->venue ?: 'Venue to be decided' }}{{ $event->city ? ', '.$event->city : '' }}</p></td><td class="px-5 py-4 text-slate-500 dark:text-slate-400">{{ $event->manager?->name ?? 'Unassigned' }}</td><td class="px-5 py-4"><span class="rounded-full bg-mist px-2.5 py-1 text-[10px] font-semibold capitalize text-forest dark:bg-forest/30 dark:text-emerald-200">{{ Str::headline(str_replace('_', ' ', $event->status)) }}</span>@if ($event->status === 'tentative_hold')<p class="mt-1 text-[10px] text-slate-400">Expires {{ $event->hold_expires_at?->diffForHumans() ?? 'unknown' }}</p>@endif</td><td class="px-5 py-4 text-right"><a class="font-semibold text-forest underline dark:text-emerald-300" href="{{ route('admin.events.show', $event) }}">Open →</a></td></tr>
            @empty
                <tr><td colspan="6" class="px-5 py-16 text-center"><h2 class="font-display text-2xl">No events yet</h2><p class="mt-2 text-sm text-slate-500">Create a tentative hold from a qualified inquiry to start the event workspace.</p><a class="mt-4 inline-block text-xs font-semibold text-forest underline dark:text-emerald-300" href="{{ route('admin.inquiries.index') }}">View inquiries →</a></td></tr>
            @endforelse
        </tbody></table></div>
        @if ($events->hasPages())<div class="border-t border-slate-100 px-5 py-4 dark:border-slate-800">{{ $events->links() }}</div>@endif
    </section>
@endsection
