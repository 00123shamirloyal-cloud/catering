<?php

namespace App\Actions;

use App\Models\Event;
use App\Models\Inquiry;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BuildQuotation
{
    public function __construct(private RecordAuditEntry $auditEntry) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data, int $actorId): Quotation
    {
        return DB::transaction(function () use ($data, $actorId): Quotation {
            $previous = isset($data['revision_of'])
                ? Quotation::query()->with('items')->lockForUpdate()->findOrFail($data['revision_of'])
                : null;
            $inquiry = isset($data['inquiry_id']) ? Inquiry::query()->findOrFail($data['inquiry_id']) : null;
            $event = isset($data['event_id']) ? Event::query()->findOrFail($data['event_id']) : null;

            if ($previous) {
                $inquiry ??= $previous->inquiry;
                $event ??= $previous->event;

                if (in_array($previous->status, ['rejected', 'expired', 'superseded'], true)) {
                    throw ValidationException::withMessages(['revision_of' => 'This quotation can no longer be revised. Start a new quotation instead.']);
                }

                if (($inquiry && $inquiry->customer_id !== $previous->customer_id)
                    || ($event && $event->customer_id !== $previous->customer_id)) {
                    throw ValidationException::withMessages(['revision_of' => 'A quotation revision must remain linked to the same customer.']);
                }
            }

            $customerId = $event?->customer_id ?? $inquiry?->customer_id ?? $previous?->customer_id;

            if (! $customerId) {
                throw ValidationException::withMessages(['inquiry_id' => 'Choose an inquiry or event before preparing a quotation.']);
            }

            $subtotal = '0.00';
            $normalizedItems = [];

            foreach ($data['items'] as $position => $item) {
                $quantity = bcadd('0', (string) $item['quantity'], 2);
                $unitPrice = bcadd('0', (string) $item['unit_price'], 2);
                $lineTotal = bcmul($quantity, $unitPrice, 2);
                $subtotal = bcadd($subtotal, $lineTotal, 2);
                $normalizedItems[] = [
                    'position' => $position + 1,
                    'description' => trim($item['description']),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $discount = bcadd('0', (string) ($data['discount_amount'] ?? '0'), 2);
            $charges = bcadd('0', (string) ($data['charges_amount'] ?? '0'), 2);

            if (bccomp($discount, $subtotal, 2) === 1) {
                throw ValidationException::withMessages(['discount_amount' => 'The discount cannot exceed the quotation subtotal.']);
            }

            $taxRate = bcadd('0', (string) ($data['tax_rate'] ?? '0'), 2);
            $taxable = bcadd(bcsub($subtotal, $discount, 2), $charges, 2);
            $taxAmount = bcdiv(bcmul($taxable, $taxRate, 4), '100', 2);
            $total = bcadd($taxable, $taxAmount, 2);
            $revisionGroup = $previous?->revision_group ?? (string) Str::uuid();
            $revisionNumber = $previous
                ? (int) Quotation::query()->where('revision_group', $revisionGroup)->max('revision_number') + 1
                : 1;

            $quotation = Quotation::query()->create([
                'inquiry_id' => $inquiry?->id,
                'event_id' => $event?->id ?? $inquiry?->event?->id,
                'customer_id' => $customerId,
                'revision_group' => $revisionGroup,
                'revision_number' => $revisionNumber,
                'currency' => $data['currency'],
                'valid_until' => $data['valid_until'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'charges_amount' => $charges,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'deposit_percent' => $data['deposit_percent'] ?? null,
                'terms' => $data['terms'] ?? null,
                'exclusions' => $data['exclusions'] ?? null,
                'status' => 'draft',
                'created_by' => $actorId,
            ]);

            $quotation->items()->createMany($normalizedItems);

            if ($previous) {
                $previous->update([
                    'status' => 'superseded',
                    'acceptance_token_hash' => null,
                    'acceptance_expires_at' => null,
                ]);
            }

            $this->auditEntry->handle($quotation, $previous ? 'Quotation revision created' : 'Quotation created', [
                'revision' => $revisionNumber,
                'total' => $total,
                'currency' => $quotation->currency,
            ]);

            return $quotation;
        });
    }
}
