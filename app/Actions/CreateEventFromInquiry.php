<?php

namespace App\Actions;

use App\Models\Event;
use App\Models\FollowUpTask;
use App\Models\Inquiry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateEventFromInquiry
{
    public function __construct(private BookingConflictChecker $conflictChecker, private RecordAuditEntry $auditEntry) {}

    /** @param array<string, mixed> $data */
    public function handle(Inquiry $inquiry, array $data, int $actorId): Event
    {
        return DB::transaction(function () use ($inquiry, $data, $actorId): Event {
            $lockedInquiry = Inquiry::query()->whereKey($inquiry->id)->lockForUpdate()->firstOrFail();
            $existingEvent = Event::query()->where('inquiry_id', $lockedInquiry->id)->first();

            if ($existingEvent) {
                return $existingEvent;
            }

            if (in_array($lockedInquiry->status, ['booked', 'lost', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'inquiry' => 'A booked, lost, or cancelled inquiry cannot start a new event hold.',
                ]);
            }

            $this->conflictChecker->assertEventVenueAvailable(
                $data['event_date'],
                $data['start_time'] ?? null,
                $data['end_time'] ?? null,
                $data['venue'] ?? null,
            );

            $holdExpiresAt = now()->addHours(config('catering.event_hold_hours'));
            $event = Event::query()->create([
                'reference' => 'EVT-'.Str::upper(Str::random(10)),
                'inquiry_id' => $lockedInquiry->id,
                'customer_id' => $lockedInquiry->customer_id,
                'title' => $data['title'],
                'category' => $data['category'],
                'event_date' => $data['event_date'],
                'start_time' => $data['start_time'] ?? null,
                'end_time' => $data['end_time'] ?? null,
                'venue' => $data['venue'] ?? null,
                'city' => $data['city'] ?? $lockedInquiry->city,
                'guest_count' => $data['guest_count'] ?? $lockedInquiry->guest_count,
                'event_manager_id' => $data['event_manager_id'] ?? ($lockedInquiry->assignedUser?->canManageEvents() ? $lockedInquiry->assigned_to : $actorId),
                'status' => 'tentative_hold',
                'held_by' => $actorId,
                'hold_expires_at' => $holdExpiresAt,
                'internal_notes' => $data['internal_notes'] ?? null,
            ]);

            if ($lockedInquiry->status === 'new') {
                $lockedInquiry->update(['status' => 'qualified']);
            }

            FollowUpTask::query()->create([
                'title' => 'Review tentative hold '.$event->reference,
                'description' => 'Confirm the customer’s requirements and quote before the hold expires.',
                'inquiry_id' => $lockedInquiry->id,
                'event_id' => $event->id,
                'customer_id' => $event->customer_id,
                'assigned_to' => $event->event_manager_id,
                'due_at' => $holdExpiresAt,
                'priority' => 'high',
                'status' => 'open',
            ]);

            $this->auditEntry->handle($event, 'Tentative event hold created from inquiry', [
                'inquiry_reference' => $lockedInquiry->reference,
                'hold_expires_at' => $holdExpiresAt->toIso8601String(),
            ]);

            return $event;
        });
    }
}
