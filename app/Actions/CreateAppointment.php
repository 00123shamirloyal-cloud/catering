<?php

namespace App\Actions;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateAppointment
{
    public function __construct(private BookingConflictChecker $conflictChecker, private RecordAuditEntry $auditEntry) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): Appointment
    {
        return DB::transaction(function () use ($data): Appointment {
            $assignee = User::query()->whereKey($data['assigned_to'])->lockForUpdate()->firstOrFail();
            abort_unless($assignee->isStaff(), 422, 'Choose an active staff member.');

            $this->conflictChecker->assertAppointmentAvailable(
                $assignee->id,
                $data['starts_at'],
                $data['ends_at'],
            );

            $appointment = Appointment::query()->create([
                ...$data,
                'status' => 'scheduled',
                'timezone' => $data['timezone'] ?? config('app.timezone'),
            ]);

            $this->auditEntry->handle($appointment, 'Appointment scheduled', [
                'starts_at' => $appointment->starts_at->toIso8601String(),
                'assigned_to' => $appointment->assigned_to,
            ]);

            return $appointment;
        });
    }
}
