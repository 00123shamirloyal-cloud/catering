@extends('layouts.admin')

@section('content')
    <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-forest dark:text-emerald-300">Stay on top of the next step</p><h1 class="mt-2 font-display text-4xl">Follow-up tasks</h1><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">New website inquiries create an internal follow-up automatically.</p></div>
    <section class="mt-7 space-y-3">
        @forelse ($tasks as $task)
            <article class="admin-card flex flex-col justify-between gap-5 sm:flex-row sm:items-center"><div class="flex min-w-0 gap-4"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-cream text-clay">✓</span><div class="min-w-0"><h2 class="truncate text-sm font-semibold">{{ $task->title }}</h2><p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $task->description ?? 'Follow up with the customer.' }}</p><div class="mt-2 flex flex-wrap gap-3 text-[11px] text-slate-400"><span>{{ $task->due_at?->format('D, M j · g:i A') ?? 'No due date' }}</span>@if ($task->customer)<a class="font-medium text-forest underline dark:text-emerald-300" href="{{ route('admin.customers.show', $task->customer) }}">{{ $task->customer->name }}</a>@endif @if ($task->inquiry)<a class="font-medium text-forest underline dark:text-emerald-300" href="{{ route('admin.inquiries.show', $task->inquiry) }}">{{ $task->inquiry->reference }}</a>@endif</div></div></div><form method="POST" action="{{ route('admin.tasks.complete', $task) }}" class="flex shrink-0 gap-2">@csrf @method('PUT')<label class="sr-only" for="completion-{{ $task->id }}">Completion note</label><input id="completion-{{ $task->id }}" class="admin-input max-w-48 text-xs" name="completion_notes" placeholder="Optional note"><button class="rounded-lg border border-forest/25 px-3 py-2 text-xs font-semibold text-forest hover:bg-mist dark:text-emerald-300" type="submit">Mark done</button></form></article>
        @empty
            <div class="admin-card py-16 text-center"><span class="text-3xl text-gold">✓</span><h2 class="mt-4 font-display text-2xl">Everything is up to date</h2><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">There are no open follow-up tasks.</p></div>
        @endforelse
    </section>
    <div class="mt-6">{{ $tasks->links() }}</div>
@endsection
