<?php

namespace App\Actions;

use App\Models\Appointment;
use App\Models\Event;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class BookingConflictChecker
{
    public function assertAppointmentAvailable(int $assigneeId, string $startsAt, string $endsAt, ?int $ignoreId = null): void
    {
        $normalizedStart = Carbon::parse($startsAt, config('app.timezone'))->format('Y-m-d H:i:s');
        $normalizedEnd = Carbon::parse($endsAt, config('app.timezone'))->format('Y-m-d H:i:s');
        $conflict = Appointment::query()
            ->where('assigned_to', $assigneeId)
            ->where('status', 'scheduled')
            ->where('starts_at', '<', $normalizedEnd)
            ->where('ends_at', '>', $normalizedStart)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->lockForUpdate()
            ->first();

        if ($conflict) {
            throw ValidationException::withMessages([
                'starts_at' => 'This employee already has an appointment during that time.',
            ]);
        }
    }

    public function assertEventVenueAvailable(string $eventDate, ?string $startTime, ?string $endTime, ?string $venue, ?int $ignoreId = null): void
    {
        if (! filled($venue)) {
            return;
        }

        $conflicts = Event::query()
            ->whereDate('event_date', $eventDate)
            ->whereRaw('LOWER(TRIM(venue)) = ?', [mb_strtolower(trim($venue))])
            ->where(function ($query): void {
                $query->whereIn('status', ['confirmed', 'planning', 'ready_for_event', 'in_progress'])
                    ->orWhere(fn ($holds) => $holds->where('status', 'tentative_hold')->where('hold_expires_at', '>', now()));
            })
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->lockForUpdate()
            ->get();

        foreach ($conflicts as $event) {
            if (! $startTime || ! $endTime || ! $event->start_time || ! $event->end_time) {
                throw ValidationException::withMessages([
                    'venue' => 'This venue already has an active event on the selected date.',
                ]);
            }

            if ($event->start_time < $endTime && $event->end_time > $startTime) {
                throw ValidationException::withMessages([
                    'venue' => 'This venue already has an event during that time.',
                ]);
            }
        }
    }
}
