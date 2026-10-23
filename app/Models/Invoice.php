<?php

namespace App\Models;

use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    protected $fillable = [
        'number', 'customer_id', 'event_id', 'quotation_id', 'currency', 'issued_at', 'due_at', 'subtotal',
        'discount_amount', 'tax_amount', 'total', 'status', 'terms', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'due_at' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('position');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(FinancialAdjustment::class);
    }

    public function amountReceived(): string
    {
        $payments = $this->payments()->where('status', 'recorded')->sum('amount');
        $refunds = $this->adjustments()->where('type', 'refund')->where('status', 'recorded')->sum('amount');

        return bcsub((string) $payments, (string) $refunds, 2);
    }

    public function balanceDue(): string
    {
        $balance = bcsub((string) $this->total, $this->amountReceived(), 2);
        $credits = $this->adjustments()->where('type', 'credit_note')->where('status', 'recorded')->sum('amount');

        return bcsub($balance, (string) $credits, 2);
    }
}
