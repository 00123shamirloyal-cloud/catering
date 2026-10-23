<?php

namespace App\Actions;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordOfflinePayment
{
    public function __construct(private RecordAuditEntry $auditEntry) {}

    /** @param array<string, mixed> $data */
    public function handle(Invoice $invoice, array $data, int $actorId): Payment
    {
        return DB::transaction(function () use ($invoice, $data, $actorId): Payment {
            $lockedInvoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if (isset($data['transaction_reference'])) {
                $existing = Payment::query()->where('transaction_reference', $data['transaction_reference'])->first();

                if ($existing) {
                    if ($existing->invoice_id === $lockedInvoice->id) {
                        if (bccomp((string) $existing->amount, (string) $data['amount'], 2) !== 0 || $existing->method !== $data['method']) {
                            throw ValidationException::withMessages(['transaction_reference' => 'This transaction reference was already recorded with different payment details.']);
                        }

                        return $existing;
                    }

                    throw ValidationException::withMessages(['transaction_reference' => 'This transaction reference is already recorded on another invoice.']);
                }
            }

            if (in_array($lockedInvoice->status, ['draft', 'cancelled'], true)) {
                throw ValidationException::withMessages(['invoice' => 'Payments can only be recorded against an issued invoice.']);
            }

            $amount = bcadd('0', (string) $data['amount'], 2);
            $balance = $lockedInvoice->balanceDue();

            if (bccomp($amount, $balance, 2) === 1) {
                throw ValidationException::withMessages(['amount' => 'The payment cannot exceed the outstanding balance.']);
            }

            $payment = Payment::query()->create([
                'invoice_id' => $lockedInvoice->id,
                'amount' => $amount,
                'currency' => $lockedInvoice->currency,
                'method' => $data['method'],
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'paid_at' => $data['paid_at'],
                'status' => 'recorded',
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $actorId,
            ]);

            $balanceAfter = $lockedInvoice->fresh()->balanceDue();
            $lockedInvoice->update([
                'status' => bccomp($balanceAfter, '0.00', 2) <= 0 ? 'paid' : 'partially_paid',
            ]);
            $this->auditEntry->handle($payment, 'Offline payment recorded', [
                'invoice_number' => $lockedInvoice->number,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
            ]);

            return $payment;
        });
    }
}
