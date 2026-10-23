<?php

namespace App\Actions;

use App\Mail\QuotationReady;
use App\Models\Quotation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendQuotation
{
    public function __construct(private RecordAuditEntry $auditEntry) {}

    /** @return array{url: string, sent: bool} */
    public function handle(Quotation $quotation): array
    {
        $quotation->loadMissing('customer', 'items');
        abort_unless(filled($quotation->customer->email), 422, 'Add a verified customer email before preparing a proposal link.');
        abort_if($quotation->valid_until?->isBefore(today()), 422, 'This quotation expired. Create a new revision before sending it.');

        $token = Str::random(64);
        $expiresAt = $quotation->valid_until?->endOfDay() ?? now()->addDays(14);
        $quotation->update([
            'acceptance_token_hash' => hash('sha256', $token),
            'acceptance_expires_at' => $expiresAt,
        ]);
        $url = route('proposals.show', ['quotation' => $quotation, 'token' => $token]);

        if (config('mail.default') === 'log') {
            $quotation->update(['sent_at' => now()]);
            $this->auditEntry->handle($quotation, 'Secure proposal link prepared; no email was sent', [
                'expires_at' => $expiresAt->toIso8601String(),
            ]);

            return ['url' => $url, 'sent' => false];
        }

        Mail::to($quotation->customer->email)->send(new QuotationReady($quotation, $url));
        $quotation->update(['status' => 'sent', 'sent_at' => now()]);
        $this->auditEntry->handle($quotation, 'Proposal email handed to the configured mail transport', [
            'expires_at' => $expiresAt->toIso8601String(),
        ]);

        return ['url' => $url, 'sent' => true];
    }
}
