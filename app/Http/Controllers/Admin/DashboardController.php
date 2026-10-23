<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\ContactMessage;
use App\Models\ContentEntry;
use App\Models\Event;
use App\Models\FollowUpTask;
use App\Models\Inquiry;
use App\Models\Quotation;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date', 'after_or_equal:start'],
        ]);
        $start = Carbon::parse($filters['start'] ?? now()->startOfMonth()->toDateString(), config('app.timezone'))->startOfDay();
        $end = Carbon::parse($filters['end'] ?? now()->endOfMonth()->toDateString(), config('app.timezone'))->endOfDay();
        $user = $request->user();
        $canViewCrm = $user->canViewCrm();

        $outstandingBalance = 0;
        if ($user->canManageFinance()) {
            $payments = DB::table('payments')->selectRaw('invoice_id, SUM(amount) AS amount')->where('status', 'recorded')->groupBy('invoice_id');
            $refunds = DB::table('financial_adjustments')->selectRaw('invoice_id, SUM(amount) AS amount')->where('type', 'refund')->where('status', 'recorded')->groupBy('invoice_id');
            $credits = DB::table('financial_adjustments')->selectRaw('invoice_id, SUM(amount) AS amount')->where('type', 'credit_note')->where('status', 'recorded')->groupBy('invoice_id');
            $balanceExpression = 'invoices.total - COALESCE(payment_totals.amount, 0) + COALESCE(refund_totals.amount, 0) - COALESCE(credit_totals.amount, 0)';
            $outstandingBalance = DB::table('invoices')
                ->leftJoinSub($payments, 'payment_totals', fn ($join) => $join->on('invoices.id', '=', 'payment_totals.invoice_id'))
                ->leftJoinSub($refunds, 'refund_totals', fn ($join) => $join->on('invoices.id', '=', 'refund_totals.invoice_id'))
                ->leftJoinSub($credits, 'credit_totals', fn ($join) => $join->on('invoices.id', '=', 'credit_totals.invoice_id'))
                ->whereNotIn('invoices.status', ['draft', 'cancelled'])
                ->selectRaw("COALESCE(SUM(CASE WHEN {$balanceExpression} > 0 THEN {$balanceExpression} ELSE 0 END), 0) AS balance")
                ->value('balance');
        }

        return view('admin.dashboard', [
            'startDate' => $start->toDateString(),
            'endDate' => $end->toDateString(),
            'newInquiries' => $canViewCrm ? Inquiry::query()->where('status', 'new')->whereBetween('created_at', [$start, $end])->count() : 0,
            'openInquiries' => $canViewCrm ? Inquiry::query()->whereNotIn('status', ['booked', 'lost', 'cancelled'])->whereBetween('created_at', [$start, $end])->count() : 0,
            'appointmentsToday' => $user->canManageAppointments()
                ? Appointment::query()->with(['customer', 'assignee'])->where('status', 'scheduled')->whereDate('starts_at', today(config('app.timezone')))->orderBy('starts_at')->get()
                : collect(),
            'upcomingEvents' => $user->canManageEvents()
                ? Event::query()->with(['customer', 'manager'])->whereBetween('event_date', [today(config('app.timezone')), $end->toDateString()])->whereNotIn('status', ['completed', 'cancelled'])->where(fn ($query) => $query->where('status', '!=', 'tentative_hold')->orWhere('hold_expires_at', '>', now()))->orderBy('event_date')->limit(6)->get()
                : collect(),
            'upcomingEventsCount' => $user->canManageEvents()
                ? Event::query()->whereBetween('event_date', [$start->toDateString(), $end->toDateString()])->whereNotIn('status', ['completed', 'cancelled'])->count()
                : 0,
            'pendingQuotations' => $user->canManageFinance()
                ? Quotation::query()->whereIn('status', ['sent', 'viewed'])->whereBetween('sent_at', [$start, $end])->count()
                : 0,
            'confirmedBookings' => $user->canManageEvents()
                ? Event::query()->whereIn('status', ['confirmed', 'planning', 'ready_for_event', 'in_progress'])->whereBetween('confirmed_at', [$start, $end])->count()
                : 0,
            'outstandingBalance' => $user->canManageFinance() ? (float) $outstandingBalance : 0,
            'currency' => SiteSetting::values()['currency_code'] ?? '',
            'followUpsDue' => $canViewCrm ? FollowUpTask::query()->where('status', 'open')->whereNotNull('due_at')->where('due_at', '<=', now())->count() : 0,
            'openMessages' => $canViewCrm ? ContactMessage::query()->where('status', 'unread')->count() : 0,
            'publishedContent' => ContentEntry::query()->where('status', 'published')->count(),
            'recentInquiries' => $canViewCrm ? Inquiry::query()->with('customer')->whereBetween('created_at', [$start, $end])->latest()->limit(6)->get() : collect(),
            'tasks' => $canViewCrm ? FollowUpTask::query()->with(['inquiry', 'event', 'assignedUser'])->whereIn('status', ['open', 'in_progress'])->orderByRaw('due_at IS NULL')->orderBy('due_at')->limit(6)->get() : collect(),
            'recentActivity' => $canViewCrm ? ActivityLog::query()->with('actor')->whereBetween('created_at', [$start, $end])->latest()->limit(7)->get() : collect(),
        ]);
    }
}
