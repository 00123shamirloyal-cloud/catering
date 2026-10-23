<?php

namespace App\Actions;

use App\Models\Invoice;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateInvoiceFromQuotation
{
    public function __construct(private RecordAuditEntry $auditEntry) {}

    /** @param array<string, mixed> $data */
    public function handle(Quotation $quotation, array $data, int $actorId): Invoice
    {
        return DB::transaction(function () use ($quotation, $data, $actorId): Invoice {
            $lockedQuotation = Quotation::query()->with('items')->whereKey($quotation->id)->lockForUpdate()->firstOrFail();

            if ($lockedQuotation->status !== 'accepted') {
                throw ValidationException::withMessages(['quotation' => 'An invoice can only be created from an accepted quotation.']);
            }

            $existingInvoice = Invoice::query()->where('quotation_id', $lockedQuotation->id)->first();

            if ($existingInvoice) {
                return $existingInvoice;
            }

            $invoice = Invoice::query()->create([
                'number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'customer_id' => $lockedQuotation->customer_id,
                'event_id' => $lockedQuotation->event_id,
                'quotation_id' => $lockedQuotation->id,
                'currency' => $lockedQuotation->currency,
                'issued_at' => now()->toDateString(),
                'due_at' => $data['due_at'] ?? null,
                'subtotal' => $lockedQuotation->subtotal,
                'discount_amount' => $lockedQuotation->discount_amount,
                'tax_amount' => $lockedQuotation->tax_amount,
                'total' => $lockedQuotation->total,
                'status' => 'unpaid',
                'terms' => $data['terms'] ?? $lockedQuotation->terms,
                'created_by' => $actorId,
            ]);

            $invoice->items()->createMany($lockedQuotation->items->map(fn ($item): array => [
                'position' => $item->position,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ])->all());

            $this->auditEntry->handle($invoice, 'Invoice created from accepted quotation', [
                'quotation_id' => $lockedQuotation->id,
                'total' => $invoice->total,
                'currency' => $invoice->currency,
            ]);

            return $invoice;
        });
    }
}
