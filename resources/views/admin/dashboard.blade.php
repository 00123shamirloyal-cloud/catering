@extends('layouts.admin')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-forest dark:text-emerald-300">Overview</p><h1 class="mt-2 font-display text-4xl">Good day, {{ Str::before(auth()->user()->name, ' ') }}.</h1><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">A live view of the work available to your role.</p></div>
        @can('view-crm')<a class="inline-flex items-center justify-center gap-2 rounded-lg bg-forest px-4 py-2.5 text-sm font-semibold text-white hover:bg-ink" href="{{ route('admin.inquiries.index') }}">Review inquiries <span aria-hidden="true">→</span></a>@endcan
    </div>

    <form class="mt-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900" method="GET" action="{{ route('admin.dashboard') }}">
        <label class="grid gap-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">From<input class="admin-input mt-1 text-sm font-normal normal-case tracking-normal" type="date" name="start" value="{{ $startDate }}"></label>
        <label class="grid gap-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Through<input class="admin-input mt-1 text-sm font-normal normal-case tracking-normal" type="date" name="end" value="{{ $endDate }}"></label>
        <button class="rounded-lg border border-slate-200 px-4 py-2.5 text-xs font-semibold dark:border-slate-700" type="submit">Apply dates</button>
        <a class="px-2 py-2.5 text-xs font-semibold text-forest underline dark:text-emerald-300" href="{{ route('admin.dashboard') }}">This month</a>
    </form>

    <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['New inquiries', $newInquiries, route('admin.inquiries.index'), 'Received in selected dates', 'view-crm'],
            ['Today’s appointments', $appointmentsToday->count(), route('admin.calendar', ['view' => 'day']), 'Scheduled for today', 'manage-appointments'],
            ['Upcoming events', $upcomingEventsCount, route('admin.events.index'), 'Active in selected dates', 'manage-events'],
            ['Pending quotations', $pendingQuotations, route('admin.quotations.index'), 'Sent and awaiting a decision', 'manage-finance'],
            ['Outstanding payments', ($currency ? $currency.' ' : '').number_format($outstandingBalance, 2), route('admin.invoices.index'), 'Current remaining invoice balances', 'manage-finance'],
            ['Confirmed bookings', $confirmedBookings, route('admin.events.index'), 'Confirmed in selected dates', 'manage-events'],
            ['Follow-ups due', $followUpsDue, route('admin.tasks.index'), 'Open tasks past their due date', 'view-crm'],
            ['Open inquiries', $openInquiries, route('admin.inquiries.index'), 'Created in selected dates', 'view-crm'],
        ] as [$label, $value, $link, $hint, $requiredGate])
            @if (! $requiredGate || auth()->user()->can($requiredGate))
                <a class="admin-card group block hover:border-forest/40" href="{{ $link }}"><p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p><p class="mt-4 font-display text-3xl">{{ is_numeric($value) ? number_format((float) $value) : $value }}</p><p class="mt-2 text-[11px] text-slate-400">{{ $hint }}</p><span class="mt-4 inline-block text-xs font-semibold text-forest group-hover:underline dark:text-emerald-300">View details →</span></a>
            @endif
        @endforeach
    </div>

    <div class="mt-7 grid gap-5 xl:grid-cols-[1.3fr_0.9fr]">
        @can('view-crm')<section class="admin-card p-0">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800"><div><h2 class="font-semibold">Recent inquiries</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $startDate }} to {{ $endDate }}</p></div><a class="text-xs font-semibold text-forest hover:underline dark:text-emerald-300" href="{{ route('admin.inquiries.index') }}">View all</a></div>
            @if ($recentInquiries->isEmpty())
                <div class="px-5 py-12 text-center"><p class="font-display text-2xl">No inquiries in this date range</p><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">New event requests submitted from the website appear here automatically.</p></div>
            @else
                <div class="overflow-x-auto"><table class="w-full min-w-[550px] text-left text-sm"><thead class="text-[10px] uppercase tracking-[0.12em] text-slate-400"><tr><th class="px-5 py-3 font-medium">Inquiry</th><th class="px-5 py-3 font-medium">Event</th><th class="px-5 py-3 font-medium">Date</th><th class="px-5 py-3 font-medium">Status</th></tr></thead><tbody class="divide-y divide-slate-100 dark:divide-slate-800">@foreach ($recentInquiries as $inquiry)<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50"><td class="px-5 py-4"><a class="font-semibold text-forest hover:underline dark:text-emerald-300" href="{{ route('admin.inquiries.show', $inquiry) }}">{{ $inquiry->customer_name }}</a><p class="mt-1 text-[11px] text-slate-400">{{ $inquiry->reference }}</p></td><td class="px-5 py-4"><p>{{ $inquiry->event_type }}</p><p class="mt-1 text-[11px] text-slate-400">{{ number_format($inquiry->guest_count) }} guests</p></td><td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ $inquiry->event_date->format('M j, Y') }}</td><td class="px-5 py-4"><span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-medium capitalize text-amber-800 dark:bg-amber-950/50 dark:text-amber-200">{{ str_replace('_', ' ', $inquiry->status) }}</span></td></tr>@endforeach</tbody></table></div>
            @endif
        </section>@endcan
        @can('manage-appointments')<section class="admin-card">
            <div class="flex items-start justify-between"><div><h2 class="font-semibold">Today’s schedule</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ now()->format('l, F j') }}</p></div><a class="text-xs font-semibold text-forest hover:underline dark:text-emerald-300" href="{{ route('admin.calendar', ['view' => 'day']) }}">Calendar</a></div>
            <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">@forelse ($appointmentsToday as $appointment)<div class="py-3 first:pt-0"><p class="text-sm font-semibold">{{ $appointment->title }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $appointment->starts_at->format('g:i A') }}–{{ $appointment->ends_at->format('g:i A') }} · {{ $appointment->customer?->name ?? 'Internal' }}</p><p class="mt-1 text-[10px] text-slate-400">Assigned to {{ $appointment->assignee->name }}</p></div>@empty<div class="py-8 text-center"><p class="font-display text-xl">No appointments today</p><p class="mt-2 text-xs text-slate-500 dark:text-slate-400">The calendar is clear for now.</p></div>@endforelse</div>
        </section>@endcan
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-3">
        @can('manage-events')<section class="admin-card"><div class="flex items-start justify-between"><div><h2 class="font-semibold">Upcoming events</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Next event dates</p></div><a class="text-xs font-semibold text-forest hover:underline dark:text-emerald-300" href="{{ route('admin.events.index') }}">View all</a></div><div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">@forelse ($upcomingEvents as $event)<a class="block py-3 first:pt-0" href="{{ route('admin.events.show', $event) }}"><p class="text-sm font-semibold">{{ $event->title }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $event->event_date->format('M j') }} · {{ $event->customer->name }}</p><p class="mt-1 text-[10px] capitalize text-slate-400">{{ str_replace('_', ' ', $event->status) }} · {{ $event->venue ?: 'Venue not set' }}</p></a>@empty<p class="py-8 text-center text-sm text-slate-500">No active events in the selected window.</p>@endforelse</div></section>@endcan
        @can('view-crm')<section class="admin-card"><div class="flex items-start justify-between"><div><h2 class="font-semibold">Follow-up tasks</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Keep conversations and planning moving</p></div><a class="text-xs font-semibold text-forest hover:underline dark:text-emerald-300" href="{{ route('admin.tasks.index') }}">View all</a></div><div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">@forelse ($tasks as $task)<div class="flex items-start gap-3 py-3 first:pt-0"><span class="mt-1 grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-cream text-xs text-clay">✓</span><div class="min-w-0 flex-1"><p class="truncate text-sm font-medium">{{ $task->title }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $task->due_at?->format('M j · g:i A') ?? 'No due date' }} · {{ $task->assignedUser?->name ?? 'Unassigned' }}</p></div></div>@empty<div class="py-8 text-center"><p class="font-display text-xl">You’re all caught up</p><p class="mt-2 text-xs text-slate-500 dark:text-slate-400">New inquiry follow-ups will show here.</p></div>@endforelse</div></section>
        <section class="admin-card"><div class="flex items-start justify-between"><div><h2 class="font-semibold">Recent activity</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Important changes during this period</p></div></div><div class="mt-4 space-y-4">@forelse ($recentActivity as $item)<div class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-gold"></span><div><p class="text-sm">{{ $item->description }}</p><p class="mt-1 text-[10px] text-slate-400">{{ $item->actor?->name ?? 'System' }} · {{ $item->created_at->format('M j · g:i A') }}</p></div></div>@empty<p class="py-8 text-center text-sm text-slate-500">No activity in this date range.</p>@endforelse</div></section>@endcan
    </div>
    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-4 text-xs leading-6 text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">Figures come from saved records. Confirmed booking value, invoice balances, and recorded payments are separate financial measures.</div>
@endsection
