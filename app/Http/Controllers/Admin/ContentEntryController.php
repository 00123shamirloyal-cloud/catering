<?php

namespace App\Http\Controllers\Admin;

use App\Actions\RecordAuditEntry;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveContentEntryRequest;
use App\Models\ContentEntry;
use App\Models\ContentEntryVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ContentEntryController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->string('type')->toString();
        $entries = ContentEntry::query()
            ->withCount('versions')
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->orderBy('type')
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.content.index', compact('entries', 'type'));
    }

    public function store(SaveContentEntryRequest $request, RecordAuditEntry $auditEntry): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['metadata'] = $data['metadata'] ?? [];
        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);
        $data['status'] = $request->user()->role === 'owner' ? 'draft' : 'pending_approval';
        $data['created_by'] = $request->user()->id;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->storePublicly('site-content', 'public');
        }

        $entry = ContentEntry::query()->create($data);
        $auditEntry->handle($entry, 'Website content created', ['type' => $entry->type, 'status' => $entry->status]);

        return to_route('admin.content.index')->with('success', 'Content saved.');
    }

    public function update(SaveContentEntryRequest $request, ContentEntry $contentEntry, RecordAuditEntry $auditEntry): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['metadata'] = $data['metadata'] ?? [];
        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);
        $data['status'] = $request->user()->role === 'owner' ? 'draft' : 'pending_approval';
        $data['published_at'] = null;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->storePublicly('site-content', 'public');
        }

        DB::transaction(function () use ($contentEntry, $data, $auditEntry, $request): void {
            $this->recordCurrentVersion($contentEntry, $request->user()->id);
            $contentEntry->update($data);
            $auditEntry->handle($contentEntry, 'Website content updated', ['status' => $contentEntry->status]);
        });

        return to_route('admin.content.index')->with('success', 'Content saved.');
    }

    public function versions(ContentEntry $contentEntry): View
    {
        $contentEntry->load(['versions.creator']);

        return view('admin.content.versions', compact('contentEntry'));
    }

    public function restore(
        Request $request,
        ContentEntry $contentEntry,
        ContentEntryVersion $version,
        RecordAuditEntry $auditEntry,
    ): RedirectResponse {
        abort_unless($version->content_entry_id === $contentEntry->id, 404);

        DB::transaction(function () use ($request, $contentEntry, $version, $auditEntry): void {
            $this->recordCurrentVersion($contentEntry, $request->user()->id);
            $snapshot = $version->snapshot;
            $snapshot['status'] = $request->user()->role === 'owner' ? 'draft' : 'pending_approval';
            $snapshot['published_at'] = null;
            $contentEntry->update($snapshot);
            $auditEntry->handle($contentEntry, 'Website content restored from version history', [
                'restored_version' => $version->version_number,
                'status' => $contentEntry->status,
            ]);
        });

        return to_route('admin.content.versions', $contentEntry)->with('success', 'Previous content restored as a draft for review.');
    }

    public function publish(ContentEntry $contentEntry, RecordAuditEntry $auditEntry): RedirectResponse
    {
        $contentEntry->update(['status' => 'published', 'published_at' => now()]);
        $auditEntry->handle($contentEntry, 'Website content published', ['type' => $contentEntry->type]);

        return back()->with('success', 'Content is now live on the public website.');
    }

    public function unpublish(ContentEntry $contentEntry, RecordAuditEntry $auditEntry): RedirectResponse
    {
        $contentEntry->update(['status' => 'draft', 'published_at' => null]);
        $auditEntry->handle($contentEntry, 'Website content unpublished', ['type' => $contentEntry->type]);

        return back()->with('success', 'Content is now a draft.');
    }

    public function destroy(ContentEntry $contentEntry, RecordAuditEntry $auditEntry): RedirectResponse
    {
        $auditEntry->handle($contentEntry, 'Website content archived', ['type' => $contentEntry->type]);
        $contentEntry->delete();

        return back()->with('success', 'Content archived.');
    }

    private function recordCurrentVersion(ContentEntry $contentEntry, int $actorId): ContentEntryVersion
    {
        return $contentEntry->versions()->create([
            'version_number' => (int) $contentEntry->versions()->max('version_number') + 1,
            'snapshot' => $contentEntry->only([
                'type', 'title', 'slug', 'category', 'excerpt', 'body', 'image_path', 'metadata',
                'status', 'is_featured', 'is_demo', 'sort_order', 'published_at',
            ]),
            'created_by' => $actorId,
        ]);
    }
}
