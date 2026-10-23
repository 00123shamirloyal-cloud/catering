<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Event;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function __invoke(Request $request): View
    {
        $dateInput = $request->string('date')->toString() ?: now()->toDateString();
        $date = CarbonImmutable::parse($dateInput, config('app.timezone'));
        $viewMode = $request->string('view')->toString();

        if (! in_array($viewMode, ['day', 'week', 'month', 'agenda'], true)) {
            $viewMode = 'month';
        }

        [$startsAt, $endsAt] = match ($viewMode) {
            'day' => [$date->startOfDay(), $date->endOfDay()],
            'week' => [$date->startOfWeek(), $date->endOfWeek()],
            'agenda' => [$date->startOfDay(), $date->addDays(30)->endOfDay()],
            default => [$date->startOfMonth()->startOfWeek(), $date->endOfMonth()->endOfWeek()],
        };

        $appointments = Appointment::query()
            ->with(['customer', 'inquiry', 'event', 'assignee'])
            ->where('status', 'scheduled')
            ->where('starts_at', '<=', $endsAt)
            ->where('ends_at', '>=', $startsAt)
            ->orderBy('starts_at')
            ->get()
            ->map(fn (Appointment $appointment): array => [
                'date' => $appointment->starts_at->toDateString(),
                'time' => $appointment->starts_at->format('g:i A').' – '.$appointment->ends_at->format('g:i A'),
                'title' => $appointment->title,
                'kind' => str_replace('_', ' ', $appointment->type),
                'status' => $appointment->status,
                'person' => $appointment->customer?->name ?? $appointment->inquiry?->customer_name ?? 'Internal appointment',
                'assigned' => $appointment->assignee->name,
                'url' => $appointment->event && auth()->user()->canManageEvents()
                    ? route('admin.events.show', $appointment->event)
                    : route('admin.appointments.edit', $appointment),
                'color' => 'gold',
            ]);

        $events = Event::query()
            ->with(['customer', 'manager'])
            ->whereBetween('event_date', [$startsAt->toDateString(), $endsAt->toDateString()])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where(function ($query): void {
                $query->where('status', '!=', 'tentative_hold')->orWhere('hold_expires_at', '>', now());
            })
            ->orderBy('event_date')
            ->get()
            ->map(fn (Event $event): array => [
                'date' => $event->event_date->toDateString(),
                'time' => $event->start_time ? substr($event->start_time, 0, 5) : 'All day',
                'title' => $event->title,
                'kind' => 'event · '.str_replace('_', ' ', $event->status),
                'status' => $event->status,
                'person' => $event->customer->name,
                'assigned' => $event->manager?->name ?? 'Unassigned',
                'url' => route('admin.events.show', $event),
                'color' => 'forest',
            ]);

        $entries = $appointments->concat($events)->sortBy([['date', 'asc'], ['time', 'asc']])->values();
        $days = CarbonPeriod::create($startsAt->startOfDay(), '1 day', $endsAt->startOfDay())->toArray();
        $entriesByDate = $entries->groupBy('date');

        return view('admin.calendar.index', compact('date', 'viewMode', 'startsAt', 'endsAt', 'entries', 'days', 'entriesByDate'));
    }
}
