<?php

namespace App\Models;

use Database\Factories\QuotationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    /** @use HasFactory<QuotationFactory> */
    use HasFactory;

    protected $fillable = [
        'inquiry_id', 'event_id', 'customer_id', 'revision_group', 'revision_number', 'currency', 'valid_until',
        'subtotal', 'discount_amount', 'charges_amount', 'tax_rate', 'tax_amount', 'total', 'deposit_percent',
        'terms', 'exclusions', 'status', 'acceptance_token_hash', 'acceptance_expires_at', 'sent_at',
        'viewed_at', 'accepted_at', 'rejected_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'valid_until' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'charges_amount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'deposit_percent' => 'decimal:2',
            'acceptance_expires_at' => 'datetime',
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('position');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(self::class, 'revision_group', 'revision_group')->orderBy('revision_number');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
