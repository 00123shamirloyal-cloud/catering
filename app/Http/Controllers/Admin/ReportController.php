<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Event;
use App\Models\FollowUpTask;
use App\Models\Inquiry;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$start, $end] = $this->dateRange($request);

        return view('admin.reports.index', [
            'startDate' => $start->toDateString(),
            'endDate' => $end->toDateString(),
            'currency' => SiteSetting::values()['currency_code'] ?? '',
            ...$this->metrics($start, $end),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$start, $end] = $this->dateRange($request);
        $report = $this->metrics($start, $end);
        $currency = SiteSetting::values()['currency_code'] ?? '';
        $rows = [
            ['Report', 'Value'],
            ['Period start', $start->toDateString()],
            ['Period end', $end->toDateString()],
            ['Total inquiries', $report['totalInquiries']],
            ['Booked inquiries', $report['bookedInquiries']],
            ['Inquiry conversion rate', $report['conversionRate'].'%'],
            ['Quotations issued', $report['quotationsIssued']],
            ['Quotations accepted', $report['quotationsAccepted']],
            ['Confirmed bookings', $report['confirmedBookings']],
            ['Confirmed booking value', $currency.' '.$report['confirmedBookingValue']],
            ['Invoices issued', $currency.' '.$report['invoicedAmount']],
            ['Payments recorded', $currency.' '.$report['paymentsReceived']],
            ['Outstanding invoice balances', $currency.' '.$report['outstandingBalance']],
            ['Upcoming events', $report['upcomingEvents']],
            ['Completed events', $report['completedEvents']],
            ['Cancelled events', $report['cancelledEvents']],
            ['Overdue follow-ups', $report['overdueFollowUps']],
            ['Appointments by status', ''],
        ];

        foreach ($report['appointmentOutcomes'] as $outcome) {
            $rows[] = [Str::headline(str_replace('_', ' ', $outcome->status)).' appointments', $outcome->total];
        }

        return response()->streamDownload(function () use ($rows): void {
            $stream = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($stream, $row);
            }

            fclose($stream);
        }, 'catering-report-'.$start->format('Ymd').'-'.$end->format('Ymd').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function dateRange(Request $request): array
    {
        $filters = $request->validate([
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date', 'after_or_equal:start'],
        ]);

        return [
            Carbon::parse($filters['start'] ?? now()->startOfMonth()->toDateString(), config('app.timezone'))->startOfDay(),
            Carbon::parse($filters['end'] ?? now()->endOfMonth()->toDateString(), config('app.timezone'))->endOfDay(),
        ];
    }

    /**
     * @return array{
     *     totalInquiries: int,
     *     bookedInquiries: int,
     *     conversionRate: string,
     *     inquirySources: Collection,
     *     quotationsIssued: int,
     *     quotationsAccepted: int,
     *     confirmedBookings: int,
     *     confirmedBookingValue: string,
     *     invoicedAmount: string,
     *     paymentsReceived: string,
     *     outstandingBalance: string,
     *     upcomingEvents: int,
     *     completedEvents: int,
     *     cancelledEvents: int,
     *     appointmentOutcomes: Collection,
     *     popularServices: Collection,
     *     popularPackages: Collection,
     *     overdueFollowUps: int,
     *     tasksCompleted: int
     * }
     */
    private function metrics(Carbon $start, Carbon $end): array
    {
        $totalInquiries = Inquiry::query()->whereBetween('created_at', [$start, $end])->count();
        $bookedInquiries = Inquiry::query()->where('status', 'booked')->whereBetween('created_at', [$start, $end])->count();
        $currentQuotations = Quotation::query()
            ->whereRaw('revision_number = (SELECT MAX(q2.revision_number) FROM quotations AS q2 WHERE q2.revision_group = quotations.revision_group)')
            ->whereBetween('sent_at', [$start, $end])
            ->whereIn('status', ['sent', 'viewed', 'accepted'])
            ->get(['id', 'status', 'event_id', 'total', 'accepted_at']);
        $confirmedEvents = Event::query()
            ->with(['quotations' => fn ($query) => $query->where('status', 'accepted')->orderByDesc('accepted_at')])
            ->whereIn('status', ['confirmed', 'planning', 'ready_for_event', 'in_progress', 'completed'])
            ->whereBetween('confirmed_at', [$start, $end])
            ->get();
        $bookingValue = $confirmedEvents->sum(function (Event $event): float {
            return (float) ($event->quotations->first()?->total ?? 0);
        });
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

        return [
            'totalInquiries' => $totalInquiries,
            'bookedInquiries' => $bookedInquiries,
            'conversionRate' => $totalInquiries > 0 ? number_format(($bookedInquiries / $totalInquiries) * 100, 1) : '0.0',
            'inquirySources' => DB::table('inquiries')->join('customers', 'customers.id', '=', 'inquiries.customer_id')
                ->whereBetween('inquiries.created_at', [$start, $end])
                ->select('customers.lead_source', DB::raw('COUNT(inquiries.id) AS total'))
                ->groupBy('customers.lead_source')->orderByDesc('total')->limit(6)->get(),
            'quotationsIssued' => $currentQuotations->count(),
            'quotationsAccepted' => $currentQuotations->where('status', 'accepted')->count(),
            'confirmedBookings' => $confirmedEvents->count(),
            'confirmedBookingValue' => number_format($bookingValue, 2, '.', ''),
            'invoicedAmount' => number_format((float) Invoice::query()->whereBetween('issued_at', [$start->toDateString(), $end->toDateString()])->whereNotIn('status', ['draft', 'cancelled'])->sum('total'), 2, '.', ''),
            'paymentsReceived' => number_format((float) DB::table('payments')->where('status', 'recorded')->whereBetween('paid_at', [$start, $end])->sum('amount'), 2, '.', ''),
            'outstandingBalance' => number_format((float) $outstandingBalance, 2, '.', ''),
            'upcomingEvents' => Event::query()->whereBetween('event_date', [today(config('app.timezone')), $end->toDateString()])->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'completedEvents' => Event::query()->where('status', 'completed')->whereBetween('event_date', [$start->toDateString(), $end->toDateString()])->count(),
            'cancelledEvents' => Event::query()->where('status', 'cancelled')->whereBetween('event_date', [$start->toDateString(), $end->toDateString()])->count(),
            'appointmentOutcomes' => Appointment::query()->whereBetween('starts_at', [$start, $end])->select('status', DB::raw('COUNT(*) AS total'))->groupBy('status')->orderBy('status')->get(),
            'popularServices' => DB::table('inquiry_content_entry')->join('content_entries', 'content_entries.id', '=', 'inquiry_content_entry.content_entry_id')
                ->join('inquiries', 'inquiries.id', '=', 'inquiry_content_entry.inquiry_id')
                ->where('inquiry_content_entry.selection_type', 'service')
                ->whereBetween('inquiries.created_at', [$start, $end])
                ->select('content_entries.title', DB::raw('COUNT(*) AS total'))
                ->groupBy('content_entries.title')->orderByDesc('total')->limit(6)->get(),
            'popularPackages' => DB::table('inquiries')->join('content_entries', 'content_entries.id', '=', 'inquiries.package_id')
                ->whereBetween('inquiries.created_at', [$start, $end])
                ->select('content_entries.title', DB::raw('COUNT(inquiries.id) AS total'))
                ->groupBy('content_entries.title')->orderByDesc('total')->limit(6)->get(),
            'overdueFollowUps' => FollowUpTask::query()->whereIn('status', ['open', 'in_progress'])->whereNotNull('due_at')->where('due_at', '<', now())->count(),
            'tasksCompleted' => FollowUpTask::query()->where('status', 'done')->whereBetween('completed_at', [$start, $end])->count(),
        ];
    }
}
