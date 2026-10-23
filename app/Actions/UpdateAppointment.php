<?php

namespace App\Actions;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateAppointment
{
    public function __construct(private BookingConflictChecker $conflictChecker, private RecordAuditEntry $auditEntry) {}

    /** @param array<string, mixed> $data */
    public function handle(Appointment $appointment, array $data): Appointment
    {
        return DB::transaction(function () use ($appointment, $data): Appointment {
            $lockedAppointment = Appointment::query()->whereKey($appointment->id)->lockForUpdate()->firstOrFail();

            if ($lockedAppointment->status !== 'scheduled') {
                throw ValidationException::withMessages(['appointment' => 'Only scheduled appointments can be rescheduled.']);
            }

            $assignee = User::query()->whereKey($data['assigned_to'])->lockForUpdate()->firstOrFail();
            abort_unless($assignee->isStaff(), 422, 'Choose an active staff member.');
            $this->conflictChecker->assertAppointmentAvailable($assignee->id, $data['starts_at'], $data['ends_at'], $lockedAppointment->id);

            $previousStart = $lockedAppointment->starts_at;
            $lockedAppointment->update([
                ...$data,
                'timezone' => $data['timezone'] ?? config('app.timezone'),
            ]);

            $this->auditEntry->handle($lockedAppointment, 'Appointment rescheduled or updated', [
                'starts_at_from' => $previousStart->toIso8601String(),
                'starts_at_to' => $lockedAppointment->starts_at->toIso8601String(),
                'assigned_to' => $lockedAppointment->assigned_to,
            ]);

            return $lockedAppointment;
        });
    }
}
