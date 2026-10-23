<?php

namespace App\Http\Controllers\Admin;

use App\Actions\RecordAuditEntry;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Inquiry::query()
            ->with('customer')
            ->when($request->string('status')->isNotEmpty(), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->string('q')->isNotEmpty(), function ($query) use ($request): void {
                $term = '%'.$request->string('q')->trim()->toString().'%';
                $query->where(function ($nested) use ($term): void {
                    $nested->where('reference', 'like', $term)
                        ->orWhere('customer_name', 'like', $term)
                        ->orWhere('customer_email', 'like', $term)
                        ->orWhere('event_type', 'like', $term);
                });
            })
            ->latest();
        $viewMode = $request->string('view')->toString() === 'kanban' ? 'kanban' : 'table';
        $totalCount = (clone $query)->count();

        if ($viewMode === 'kanban') {
            $inquiries = (clone $query)->limit(500)->get();
            $inquiriesByStatus = $inquiries->groupBy('status');
        } else {
            $inquiries = $query->paginate(12)->withQueryString();
            $inquiriesByStatus = collect();
        }

        return view('admin.inquiries.index', compact('inquiries', 'inquiriesByStatus', 'viewMode', 'totalCount'));
    }

    public function show(Inquiry $inquiry): View
    {
        $inquiry->load(['customer', 'package', 'serviceSelections', 'tasks.assignedUser', 'assignedUser', 'event']);
        $activity = ActivityLog::query()
            ->where('subject_type', Inquiry::class)
            ->where('subject_id', $inquiry->id)
            ->with('actor')
            ->latest()
            ->limit(15)
            ->get();

        return view('admin.inquiries.show', compact('inquiry', 'activity'));
    }

    public function update(Request $request, Inquiry $inquiry, RecordAuditEntry $auditEntry): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', Inquiry::STATUSES)],
            'next_follow_up_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);
        $oldStatus = $inquiry->status;
        $inquiry->update([
            'status' => $data['status'],
            'next_follow_up_at' => array_key_exists('next_follow_up_at', $data) ? $data['next_follow_up_at'] : $inquiry->next_follow_up_at,
        ]);

        $auditEntry->handle($inquiry, 'Inquiry updated by staff', [
            'status_from' => $oldStatus,
            'status_to' => $inquiry->status,
            'note' => $data['note'] ?? null,
        ]);

        return to_route('admin.inquiries.show', $inquiry)->with('success', 'Inquiry updated.');
    }
}
