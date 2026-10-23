<?php

namespace App\Models;

use Database\Factories\FinancialAdjustmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialAdjustment extends Model
{
    /** @use HasFactory<FinancialAdjustmentFactory> */
    use HasFactory;

    protected $fillable = ['invoice_id', 'payment_id', 'type', 'amount', 'currency', 'reference', 'reason', 'status', 'recorded_by'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
