<?php

namespace App\Http\Controllers\Admin;

use App\Actions\BookingConflictChecker;
use App\Actions\CreateEventFromInquiry;
use App\Actions\RecordAuditEntry;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEventFromInquiryRequest;
use App\Http\Requests\UpdateEventStatusRequest;
use App\Models\Event;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::query()->with(['customer', 'manager'])->orderByDesc('event_date')->paginate(15);

        return view('admin.events.index', compact('events'));
    }

    public function show(Event $event): View
    {
        $event->load(['customer', 'inquiry', 'manager', 'appointments.assignee', 'quotations.items', 'invoices.payments', 'tasks']);

        return view('admin.events.show', compact('event'));
    }

    public function createFromInquiry(CreateEventFromInquiryRequest $request, Inquiry $inquiry, CreateEventFromInquiry $createEvent): RedirectResponse
    {
        $event = $createEvent->handle($inquiry, $request->validated(), $request->user()->id);

        return to_route('admin.events.show', $event)->with('success', 'A tentative hold was created. The event is not confirmed.');
    }

    public function updateStatus(
        UpdateEventStatusRequest $request,
        Event $event,
        BookingConflictChecker $conflictChecker,
        RecordAuditEntry $auditEntry,
    ): RedirectResponse {
        $data = $request->validated();
        $allowed = Event::TRANSITIONS[$event->status] ?? [];

        if (! in_array($data['status'], $allowed, true)) {
            throw ValidationException::withMessages(['status' => 'That event status change is not allowed.']);
        }

        DB::transaction(function () use ($event, $data, $conflictChecker, $auditEntry): void {
            $lockedEvent = Event::query()->whereKey($event->id)->lockForUpdate()->firstOrFail();

            if ($lockedEvent->status === 'tentative_hold' && $lockedEvent->hold_expires_at?->isPast()) {
                throw ValidationException::withMessages(['status' => 'This tentative hold expired. Create a new hold before confirming the event.']);
            }

            if ($data['status'] === 'confirmed') {
                if (! $lockedEvent->quotations()->where('status', 'accepted')->exists()) {
                    throw ValidationException::withMessages(['status' => 'An accepted quotation is required before confirming this event.']);
                }

                $conflictChecker->assertEventVenueAvailable(
                    $lockedEvent->event_date->toDateString(),
                    $lockedEvent->start_time ? substr($lockedEvent->start_time, 0, 5) : null,
                    $lockedEvent->end_time ? substr($lockedEvent->end_time, 0, 5) : null,
                    $lockedEvent->venue,
                    $lockedEvent->id,
                );
            }

            $previousStatus = $lockedEvent->status;
            $lockedEvent->update([
                'status' => $data['status'],
                'internal_notes' => $data['internal_notes'] ?? $lockedEvent->internal_notes,
                'confirmed_at' => $data['status'] === 'confirmed' ? now() : $lockedEvent->confirmed_at,
                'hold_expires_at' => $data['status'] === 'confirmed' ? null : $lockedEvent->hold_expires_at,
            ]);
            $auditEntry->handle($lockedEvent, 'Event status changed', [
                'status_from' => $previousStatus,
                'status_to' => $lockedEvent->status,
            ]);

            if ($data['status'] === 'confirmed' && $lockedEvent->inquiry_id) {
                $inquiry = Inquiry::query()->whereKey($lockedEvent->inquiry_id)->lockForUpdate()->first();

                if ($inquiry && $inquiry->status !== 'booked') {
                    $previousInquiryStatus = $inquiry->status;
                    $inquiry->update(['status' => 'booked']);
                    $auditEntry->handle($inquiry, 'Inquiry converted to confirmed booking', [
                        'status_from' => $previousInquiryStatus,
                        'event_reference' => $lockedEvent->reference,
                    ]);
                }
            }
        });

        return to_route('admin.events.show', $event)->with('success', 'Event status updated.');
    }
}
