<?php

namespace App\Http\Controllers\Admin;

use App\Actions\BuildQuotation;
use App\Actions\RecordAuditEntry;
use App\Actions\SendQuotation;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuotationRequest;
use App\Models\Event;
use App\Models\Inquiry;
use App\Models\Quotation;
use App\Models\SiteSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class QuotationController extends Controller
{
    public function index(): View
    {
        $quotations = Quotation::query()->with(['customer', 'inquiry', 'event'])->latest()->paginate(15);

        return view('admin.quotations.index', compact('quotations'));
    }

    public function createForInquiry(Inquiry $inquiry): View
    {
        $inquiry->load(['customer', 'event']);

        return view('admin.quotations.create', [
            'inquiry' => $inquiry,
            'event' => $inquiry->event,
            'revision' => null,
            'currency' => SiteSetting::values()['currency_code'] ?? '',
        ]);
    }

    public function createForEvent(Event $event): View
    {
        $event->load(['customer', 'inquiry']);

        return view('admin.quotations.create', [
            'inquiry' => $event->inquiry,
            'event' => $event,
            'revision' => null,
            'currency' => SiteSetting::values()['currency_code'] ?? '',
        ]);
    }

    public function revise(Quotation $quotation): View
    {
        $quotation->load(['inquiry', 'event', 'customer', 'items']);

        return view('admin.quotations.create', [
            'inquiry' => $quotation->inquiry,
            'event' => $quotation->event,
            'revision' => $quotation,
            'currency' => $quotation->currency,
        ]);
    }

    public function store(StoreQuotationRequest $request, BuildQuotation $buildQuotation): RedirectResponse
    {
        $quotation = $buildQuotation->handle($request->validated(), $request->user()->id);

        return to_route('admin.quotations.show', $quotation)->with('success', 'Quotation revision saved as a draft.');
    }

    public function show(Quotation $quotation): View
    {
        $quotation->load(['customer', 'inquiry', 'event', 'items', 'revisions', 'invoices']);

        return view('admin.quotations.show', compact('quotation'));
    }

    public function downloadPdf(Quotation $quotation): Response
    {
        $quotation->load(['customer', 'event', 'inquiry', 'items']);

        return Pdf::loadView('pdf.quotation', compact('quotation'))
            ->setPaper('a4')
            ->download('proposal-'.$quotation->id.'-revision-'.$quotation->revision_number.'.pdf');
    }

    public function send(Quotation $quotation, SendQuotation $sendQuotation): RedirectResponse
    {
        if ($quotation->status !== 'draft' && $quotation->status !== 'sent') {
            throw ValidationException::withMessages(['quotation' => 'Only a draft or previously sent proposal can be sent again.']);
        }

        $result = $sendQuotation->handle($quotation);

        if ($result['sent']) {
            return to_route('admin.quotations.show', $quotation)->with('success', 'Proposal handed to the configured email transport.');
        }

        return to_route('admin.quotations.show', $quotation)->with('share_link', $result['url']);
    }

    public function showProposal(string $quotation, string $token): View
    {
        $record = Quotation::query()->with(['customer', 'items'])->findOrFail($quotation);
        $this->ensureValidToken($record, $token);

        if ($record->status === 'sent' && ! $record->viewed_at) {
            $record->update(['status' => 'viewed', 'viewed_at' => now()]);
        }

        return view('public.proposal', ['quotation' => $record, 'token' => $token]);
    }

    public function downloadProposalPdf(string $quotation, string $token): Response
    {
        $record = Quotation::query()->with(['customer', 'event', 'inquiry', 'items'])->findOrFail($quotation);
        $this->ensureValidToken($record, $token);

        return Pdf::loadView('pdf.quotation', ['quotation' => $record])
            ->setPaper('a4')
            ->download('proposal-'.$record->id.'-revision-'.$record->revision_number.'.pdf');
    }

    public function acceptProposal(Request $request, string $quotation, string $token, RecordAuditEntry $auditEntry): View
    {
        $request->validate(['accept_terms' => ['accepted']]);

        $record = DB::transaction(function () use ($quotation, $token, $auditEntry): Quotation {
            $record = Quotation::query()->whereKey($quotation)->lockForUpdate()->firstOrFail();
            $this->ensureValidToken($record, $token);

            if (! in_array($record->status, ['draft', 'sent', 'viewed'], true)) {
                throw ValidationException::withMessages(['quotation' => 'This proposal can no longer be accepted.']);
            }

            $record->update(['status' => 'accepted', 'accepted_at' => now()]);
            $auditEntry->handle($record, 'Customer accepted quotation revision', [
                'revision' => $record->revision_number,
                'accepted_at' => now()->toIso8601String(),
            ]);

            return $record->fresh(['customer', 'items']);
        });

        return view('public.proposal-accepted', ['quotation' => $record]);
    }

    private function ensureValidToken(Quotation $quotation, string $token): void
    {
        abort_unless(
            $quotation->acceptance_token_hash
                && hash_equals($quotation->acceptance_token_hash, hash('sha256', $token))
                && $quotation->acceptance_expires_at?->isFuture()
                && (! $quotation->valid_until || $quotation->valid_until->endOfDay()->isFuture()),
            404,
        );
        abort_if(in_array($quotation->status, ['rejected', 'expired', 'superseded'], true), 404);
    }
}
