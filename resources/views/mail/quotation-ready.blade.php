<x-mail::message>
# Your event proposal is ready

Hello {{ $quotation->customer->name }},

We have prepared proposal revision {{ $quotation->revision_number }} for your event inquiry. The proposal is valid until {{ $quotation->valid_until?->format('F j, Y') ?? 'the date shown in the proposal' }}.

**Proposal total:** {{ $quotation->currency }} {{ number_format((float) $quotation->total, 2) }}

<x-mail::button :url="$acceptanceUrl">
Review proposal
</x-mail::button>

The link is private and expires {{ $quotation->acceptance_expires_at->format('F j, Y') }}. Reviewing or accepting the proposal does not confirm the event until the team completes the booking confirmation process.

Thanks,<br>
{{ $quotation->customer->email }}
</x-mail::message>
