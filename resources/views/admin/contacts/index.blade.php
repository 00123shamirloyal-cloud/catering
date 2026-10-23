@extends('layouts.admin')

@section('content')
    <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-forest dark:text-emerald-300">Messages</p><h1 class="mt-2 font-display text-4xl">Contact messages</h1><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Public contact form submissions are saved here.</p></div>
    <section class="admin-card mt-7 p-0">
        @if ($messages->isEmpty())<div class="px-5 py-16 text-center"><h2 class="font-display text-2xl">No messages yet</h2><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Messages sent through the website will appear here.</p></div>@else
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($messages as $message)
                    <article class="flex flex-col justify-between gap-5 p-5 sm:flex-row sm:items-start"><div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h2 class="font-semibold">{{ $message->subject ?: 'Website message' }}</h2><span class="rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $message->status === 'unread' ? 'bg-amber-50 text-amber-800 dark:bg-amber-950/50 dark:text-amber-200' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">{{ Str::headline($message->status) }}</span></div><p class="mt-1 text-xs text-slate-500">{{ $message->name }} · <a class="underline" href="mailto:{{ $message->email }}">{{ $message->email }}</a>@if ($message->phone) · <a class="underline" href="tel:{{ $message->phone }}">{{ $message->phone }}</a>@endif</p><p class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $message->message }}</p><p class="mt-3 text-[11px] text-slate-400">{{ $message->created_at->format('M j, Y · g:i A') }}</p></div>@if ($message->status !== 'handled')<form method="POST" action="{{ route('admin.contacts.handled', $message) }}">@csrf @method('PUT')<button class="shrink-0 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" type="submit">Mark handled</button></form>@endif</article>
                @endforeach
            </div>
            <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">{{ $messages->links() }}</div>
        @endif
    </section>
@endsection
