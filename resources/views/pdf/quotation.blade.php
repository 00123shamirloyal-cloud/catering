<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 42px 46px; }
        body { color: #1d2823; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.55; }
        h1, h2, p { margin: 0; }
        h1 { margin-top: 9px; color: #0b3d2e; font-family: DejaVu Serif, serif; font-size: 26px; font-weight: normal; }
        h2 { margin-bottom: 8px; color: #0b3d2e; font-size: 11px; }
        .brand { color: #0b3d2e; font-size: 10px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; }
        .subtitle { margin-top: 5px; color: #67736e; }
        .meta { margin-top: 22px; padding: 12px 14px; background: #f2ecdf; }
        .meta table, .items, .totals { width: 100%; border-collapse: collapse; }
        .meta td { width: 50%; padding: 3px 0; vertical-align: top; }
        .items { margin-top: 22px; }
        .items th { padding: 8px 5px; border-bottom: 1px solid #dce1dc; color: #67736e; font-size: 8px; text-align: left; text-transform: uppercase; }
        .items td { padding: 9px 5px; border-bottom: 1px solid #edf0ed; vertical-align: top; }
        .right { text-align: right !important; }
        .totals { width: 58%; margin: 16px 0 0 auto; }
        .totals td { padding: 4px 3px; }
        .grand td { border-top: 1px solid #aeb8b1; padding-top: 9px; font-size: 13px; font-weight: bold; }
        .sections { margin-top: 28px; }
        .section { margin-top: 18px; }
        .muted { color: #67736e; }
        .notice { margin-top: 24px; padding: 10px; border-left: 3px solid #cbb071; background: #fbf9f3; color: #58645e; }
        .footer { margin-top: 32px; border-top: 1px solid #dce1dc; padding-top: 8px; color: #89928d; font-size: 8px; }
    </style>
</head>
<body>
    <p class="brand">{{ config('app.name') }}</p>
    <h1>Event proposal</h1>
    <p class="subtitle">Revision {{ $quotation->revision_number }} · Prepared {{ $quotation->created_at->format('F j, Y') }}</p>
    <div class="meta"><table><tr><td><strong>Prepared for</strong><br>{{ $quotation->customer->name }}<br>{{ $quotation->customer->email }}</td><td><strong>Event</strong><br>{{ $quotation->event?->title ?? $quotation->inquiry?->event_type ?? 'Custom event' }}<br>{{ $quotation->event?->event_date?->format('F j, Y') ?? $quotation->inquiry?->event_date?->format('F j, Y') ?? 'Date to be confirmed' }}</td></tr><tr><td><strong>Valid until</strong><br>{{ $quotation->valid_until?->format('F j, Y') ?? 'Not specified' }}</td><td><strong>Currency</strong><br>{{ $quotation->currency }}</td></tr></table></div>
    <table class="items"><thead><tr><th>Description</th><th class="right">Qty</th><th class="right">Unit price</th><th class="right">Amount</th></tr></thead><tbody>@foreach ($quotation->items as $item)<tr><td>{{ $item->description }}</td><td class="right">{{ number_format((float) $item->quantity, 2) }}</td><td class="right">{{ $quotation->currency }} {{ number_format((float) $item->unit_price, 2) }}</td><td class="right">{{ $quotation->currency }} {{ number_format((float) $item->line_total, 2) }}</td></tr>@endforeach</tbody></table>
    <table class="totals"><tr><td class="muted">Subtotal</td><td class="right">{{ $quotation->currency }} {{ number_format((float) $quotation->subtotal, 2) }}</td></tr><tr><td class="muted">Discount</td><td class="right">− {{ $quotation->currency }} {{ number_format((float) $quotation->discount_amount, 2) }}</td></tr><tr><td class="muted">Additional charges</td><td class="right">{{ $quotation->currency }} {{ number_format((float) $quotation->charges_amount, 2) }}</td></tr><tr><td class="muted">Tax ({{ number_format((float) $quotation->tax_rate, 2) }}%)</td><td class="right">{{ $quotation->currency }} {{ number_format((float) $quotation->tax_amount, 2) }}</td></tr><tr class="grand"><td>Total</td><td class="right">{{ $quotation->currency }} {{ number_format((float) $quotation->total, 2) }}</td></tr>@if ($quotation->deposit_percent)<tr><td class="muted">Suggested deposit ({{ $quotation->deposit_percent }}%)</td><td class="right">{{ $quotation->currency }} {{ number_format((float) ($quotation->total * $quotation->deposit_percent / 100), 2) }}</td></tr>@endif</table>
    <div class="sections"><div class="section"><h2>Terms</h2><p>{{ $quotation->terms ?: 'No additional terms are listed.' }}</p></div><div class="section"><h2>Exclusions</h2><p>{{ $quotation->exclusions ?: 'No exclusions are listed.' }}</p></div></div>
    <p class="notice">Proposal acceptance records approval of this quotation. It does not itself confirm or reserve the event date. The team will confirm the booking separately.</p>
    <p class="footer">This proposal is a saved revision snapshot. Changes to the current service or package catalog do not alter this document.</p>
</body>
</html>
