<?php

namespace App\Models;

use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Inquiry extends Model
{
    /** @use HasFactory<InquiryFactory> */
    use HasFactory;

    public const STATUSES = [
        'new', 'contacted', 'qualified', 'consultation_scheduled', 'quotation_in_progress',
        'quotation_sent', 'awaiting_decision', 'booked', 'lost', 'cancelled',
    ];

    protected $fillable = [
        'reference', 'idempotency_token', 'customer_id', 'customer_name', 'customer_email',
        'customer_phone', 'preferred_contact', 'event_type', 'event_date', 'alternative_date',
        'start_time', 'end_time', 'guest_count', 'city', 'venue', 'setting', 'budget_range',
        'package_id', 'dietary_preferences', 'requirements', 'message', 'privacy_accepted', 'status',
        'assigned_to', 'next_follow_up_at', 'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date', 'alternative_date' => 'date', 'privacy_accepted' => 'boolean',
            'next_follow_up_at' => 'datetime', 'submitted_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ContentEntry::class, 'package_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function serviceSelections(): BelongsToMany
    {
        return $this->belongsToMany(ContentEntry::class, 'inquiry_content_entry')->withPivot('selection_type');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(FollowUpTask::class);
    }

    public function event(): HasOne
    {
        return $this->hasOne(Event::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }
}
