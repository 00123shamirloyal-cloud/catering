<?php

namespace App\Actions;

use App\Models\FinancialAdjustment;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordFinancialAdjustment
{
    public function __construct(private RecordAuditEntry $auditEntry) {}

    /** @param array<string, mixed> $data */
    public function handle(Invoice $invoice, array $data, int $actorId): FinancialAdjustment
    {
        return DB::transaction(function () use ($invoice, $data, $actorId): FinancialAdjustment {
            $lockedInvoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if (in_array($lockedInvoice->status, ['draft', 'cancelled'], true)) {
                throw ValidationException::withMessages(['invoice' => 'Adjustments can only be recorded against an issued invoice.']);
            }

            if (filled($data['reference'] ?? null)) {
                $existing = FinancialAdjustment::query()->where('reference', $data['reference'])->first();

                if ($existing) {
                    if ($existing->invoice_id === $lockedInvoice->id
                        && $existing->type === $data['type']
                        && $existing->payment_id === ($data['payment_id'] ?? null)
                        && bccomp((string) $existing->amount, (string) $data['amount'], 2) === 0) {
                        return $existing;
                    }

                    throw ValidationException::withMessages(['reference' => 'This adjustment reference is already in use.']);
                }
            }

            $amount = bcadd('0', (string) $data['amount'], 2);
            $paymentId = null;

            if ($data['type'] === 'refund') {
                $payment = Payment::query()
                    ->whereKey($data['payment_id'])
                    ->where('invoice_id', $lockedInvoice->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($payment->status !== 'recorded') {
                    throw ValidationException::withMessages(['payment_id' => 'Only a recorded payment can be refunded.']);
                }

                $alreadyRefunded = FinancialAdjustment::query()
                    ->where('payment_id', $payment->id)
                    ->where('type', 'refund')
                    ->where('status', 'recorded')
                    ->sum('amount');
                $refundable = bcsub((string) $payment->amount, (string) $alreadyRefunded, 2);

                if (bccomp($amount, $refundable, 2) === 1) {
                    throw ValidationException::withMessages(['amount' => 'The refund cannot exceed the unrefunded amount of this payment.']);
                }

                $paymentId = $payment->id;
            } elseif (bccomp($amount, $lockedInvoice->balanceDue(), 2) === 1) {
                throw ValidationException::withMessages(['amount' => 'The credit note cannot exceed the outstanding balance.']);
            }

            $adjustment = FinancialAdjustment::query()->create([
                'invoice_id' => $lockedInvoice->id,
                'payment_id' => $paymentId,
                'type' => $data['type'],
                'amount' => $amount,
                'currency' => $lockedInvoice->currency,
                'reference' => $data['reference'] ?? null,
                'reason' => $data['reason'],
                'status' => 'recorded',
                'recorded_by' => $actorId,
            ]);
            $balance = $lockedInvoice->fresh()->balanceDue();
            $netReceived = $lockedInvoice->fresh()->amountReceived();

            if (bccomp($balance, '0.00', 2) <= 0) {
                $lockedInvoice->update(['status' => 'settled']);
            } else {
                $lockedInvoice->update([
                    'status' => bccomp($netReceived, '0.00', 2) > 0 ? 'partially_paid' : 'unpaid',
                ]);
            }

            $this->auditEntry->handle($adjustment, $data['type'] === 'refund' ? 'Refund recorded' : 'Credit note recorded', [
                'invoice_number' => $lockedInvoice->number,
                'amount' => $amount,
                'currency' => $lockedInvoice->currency,
                'payment_id' => $paymentId,
            ]);

            return $adjustment;
        });
    }
}
