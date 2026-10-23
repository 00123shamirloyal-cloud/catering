@extends('layouts.admin')

@section('content')
    <a class="text-xs font-semibold text-forest underline dark:text-emerald-300" href="{{ route('admin.content.index', ['type' => $contentEntry->type]) }}">← Website content</a>
    <div class="mt-4 flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-forest dark:text-emerald-300">{{ Str::headline(str_replace('_', ' ', $contentEntry->type)) }} · Version history</p><h1 class="mt-2 font-display text-4xl">{{ $contentEntry->title }}</h1><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Restore any saved version as a draft for approval.</p></div><span class="rounded-full bg-mist px-3 py-1.5 text-xs font-semibold capitalize text-forest dark:bg-forest/30 dark:text-emerald-200">{{ Str::headline(str_replace('_', ' ', $contentEntry->status)) }}</span></div>
    <section class="admin-card mt-7"><h2 class="font-semibold">Saved versions</h2><div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
        @forelse ($contentEntry->versions as $version)
            <article class="flex flex-col justify-between gap-4 py-4 sm:flex-row sm:items-start"><div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h3 class="text-sm font-semibold">Version {{ $version->version_number }} · {{ $version->snapshot['title'] ?? 'Untitled' }}</h3><span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] capitalize text-slate-500 dark:bg-slate-800">{{ Str::headline(str_replace('_', ' ', $version->snapshot['status'] ?? 'draft')) }}</span></div><p class="mt-1 text-xs text-slate-400">{{ $version->creator?->name ?? 'Staff' }} · {{ $version->created_at->format('M j, Y · g:i A') }}</p><p class="mt-2 max-w-3xl whitespace-pre-line text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $version->snapshot['excerpt'] ?? '' }}</p></div><form method="POST" action="{{ route('admin.content.versions.restore', [$contentEntry, $version]) }}" onsubmit="return confirm('Restore this version as a draft?')">@csrf<button class="rounded-lg border border-forest/25 px-3 py-2 text-xs font-semibold text-forest dark:border-emerald-700 dark:text-emerald-300" type="submit">Restore version</button></form></article>
        @empty
            <div class="py-12 text-center"><h2 class="font-display text-2xl">No earlier versions saved</h2><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">The first version is created the next time this item is edited.</p></div>
        @endforelse
    </div></section>
@endsection
