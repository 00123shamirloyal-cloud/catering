<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreateAppointment;
use App\Actions\RecordAuditEntry;
use App\Actions\UpdateAppointment;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(): RedirectResponse
    {
        return to_route('admin.calendar');
    }

    public function create(): View
    {
        return view('admin.appointments.create', [
            'appointment' => null,
            'customers' => Customer::query()->orderBy('name')->limit(200)->get(),
            'inquiries' => Inquiry::query()->whereNotIn('status', ['booked', 'lost', 'cancelled'])->latest()->limit(200)->get(),
            'events' => Event::query()->whereNotIn('status', ['completed', 'cancelled'])->orderBy('event_date')->limit(200)->get(),
            'staff' => User::query()->where('is_active', true)->whereIn('role', ['owner', 'manager', 'sales', 'operations'])->orderBy('name')->get(),
            'appointmentTypes' => config('catering.appointment_types'),
        ]);
    }

    public function edit(Appointment $appointment): View
    {
        return view('admin.appointments.create', [
            'appointment' => $appointment,
            'customers' => Customer::query()->orderBy('name')->limit(200)->get(),
            'inquiries' => Inquiry::query()->whereNotIn('status', ['booked', 'lost', 'cancelled'])->latest()->limit(200)->get(),
            'events' => Event::query()->whereNotIn('status', ['completed', 'cancelled'])->orderBy('event_date')->limit(200)->get(),
            'staff' => User::query()->where('is_active', true)->whereIn('role', ['owner', 'manager', 'sales', 'operations'])->orderBy('name')->get(),
            'appointmentTypes' => config('catering.appointment_types'),
        ]);
    }

    public function store(StoreAppointmentRequest $request, CreateAppointment $createAppointment): RedirectResponse
    {
        $appointment = $createAppointment->handle($request->validated());

        return to_route('admin.calendar', ['date' => $appointment->starts_at->toDateString(), 'view' => 'day'])
            ->with('success', 'Appointment scheduled.');
    }

    public function update(StoreAppointmentRequest $request, Appointment $appointment, UpdateAppointment $updateAppointment): RedirectResponse
    {
        $appointment = $updateAppointment->handle($appointment, $request->validated());

        return to_route('admin.calendar', ['date' => $appointment->starts_at->toDateString(), 'view' => 'day'])
            ->with('success', 'Appointment updated.');
    }

    public function cancel(Appointment $appointment, RecordAuditEntry $auditEntry): RedirectResponse
    {
        abort_unless($appointment->status === 'scheduled', 422, 'Only a scheduled appointment can be cancelled.');
        $appointment->update(['status' => 'cancelled']);
        $auditEntry->handle($appointment, 'Appointment cancelled', ['starts_at' => $appointment->starts_at->toIso8601String()]);

        return to_route('admin.calendar', ['date' => $appointment->starts_at->toDateString(), 'view' => 'day'])
            ->with('success', 'Appointment cancelled.');
    }
}
